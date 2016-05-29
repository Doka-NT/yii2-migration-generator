<?php
/**
 * @author Soshnikov Artem <213036@skobka.com>
 * @copyright (c) 29.05.2016
 */

namespace skobka\yii2\migration;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use skobka\yii2\annotation as Annotation;
use yii\db\ColumnSchema;
use yii\db\TableSchema;

class Generator
{
    /**
     * @var AnnotationReader
     */
    protected $reader;

    protected $ignoreAnnotations = ['website', 'project'];

    protected $tabSpace = '            ';

    public function __construct()
    {
        $this->reader = new AnnotationReader();
        AnnotationRegistry::registerLoader('class_exists');
        $this->addGlobalIgnoreAnnotationNames($this->ignoreAnnotations);
    }

    /**
     * Add ignore annotation name
     * If annotation reader finds an undefined annotation it will throw an exception of Doctrine\Common\Annotations\AnnotationException
     * @param array $names
     */
    public function addGlobalIgnoreAnnotationNames(array $names)
    {
        foreach ($names as $name) {
            AnnotationReader::addGlobalIgnoredName($name);
        }
    }

    /**
     * Generate migration from annotations
     * @param string $class
     * @param string $targetDir
     * @param TableSchema $tableSchema
     */
    public function generate($class, $targetDir, $tableSchema)
    {
        $reflectionClass = $this->getReflectionClass($class);
        $annotations = $this->getNestedAnnotations($reflectionClass);
        $fileContents = $this->generateFileContents($reflectionClass, $annotations, $tableSchema);
        $fileName = $this->getFileName($class);
        file_put_contents($targetDir . DIRECTORY_SEPARATOR . $fileName . '.php', $fileContents);
    }

    /**
     * @param $class
     * @return \ReflectionClass
     */
    protected function getReflectionClass($class)
    {
        return new \ReflectionClass($class);
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param bool $notOnlyParent
     * @return Annotation\Annotation[]
     */
    protected function getNestedAnnotations(\ReflectionClass $reflectionClass, $notOnlyParent = true)
    {
        $annotations = [];
        if ($notOnlyParent) {
            $annotations = $this->reader->getClassAnnotations($reflectionClass);
            $this->checkAnnotations($annotations);
        }
        $parent = $reflectionClass->getParentClass();
        if ($parent) {
            /* @var $superclassAnnotation Annotation\Superclass */
            $superclassAnnotation = $this->reader->getClassAnnotation($parent, Annotation\Superclass::class);
            if ($superclassAnnotation) {
                $annotations = array_merge_recursive(
                    $annotations,
                    $this->getNestedAnnotations($parent, $superclassAnnotation->active)
                );
            }
        }
        return $annotations;
    }

    /**
     * @param Annotation\Annotation[] $annotations
     * @throws Annotation\exceptions\OneTableAnnotationAllowedException
     * @throws Annotation\exceptions\UndefinedColumnNameException
     * @throws Annotation\exceptions\UndefinedTableNameException
     */
    protected function checkAnnotations($annotations)
    {
        $tableAnnotationCount = 0;
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Annotation\Table) {
                if (!$annotation->name && !is_null($annotation->name)) {
                    throw new Annotation\exceptions\UndefinedTableNameException;
                }
                $tableAnnotationCount++;
            } else {
                if ($annotation instanceof Annotation\Column) {
                    if (!$annotation->name) {
                        throw new Annotation\exceptions\UndefinedColumnNameException;
                    }
                }
            }
        }

        if ($tableAnnotationCount > 1) {
            throw new Annotation\exceptions\OneTableAnnotationAllowedException;
        }
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param Annotation\Annotation[] $annotations
     * @param TableSchema $tableSchema
     * @return string
     */
    protected function generateFileContents(\ReflectionClass $reflectionClass, $annotations, $tableSchema)
    {
        if (is_null($tableSchema)) {
            return $this->generateNewTableFileContents($reflectionClass, $annotations);
        } else {
            return $this->generateAlterTableFileContents($reflectionClass, $annotations, $tableSchema);
        }
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param $annotations
     * @return string
     */
    protected function generateNewTableFileContents(\ReflectionClass $reflectionClass, $annotations)
    {
        $table = null;
        $columns = [];
        foreach ($annotations as $annotation) {
            if (($annotation instanceof Annotation\Table) && is_null($table)) {
                $table = $annotation->name ?: false;
            } elseif ($annotation instanceof Annotation\Column) {
                $columns[] = '"' . $annotation->name . '" => ' . $this->generateNewColumnString($annotation);
            }
        }
        $fileContents = file_get_contents(__DIR__ . '/resources/template.txt');
        $fileContents = str_replace([
            '__ClassNameFull__',
            '__ClassName__',
            '__TableName__',
            '__Columns__',
        ], [
            $reflectionClass->getName(),
            $this->getFileName($reflectionClass->getName()),
            $table ? "'$table'" : $this->getTableName($reflectionClass),
            implode(',' . PHP_EOL . $this->tabSpace, $columns),
        ], $fileContents);
        return $fileContents;
    }

    protected function generateAlterTableFileContents(
        \ReflectionClass $reflectionClass,
        $annotations,
        TableSchema $tableSchema
    ) {
        $removeColumns = [];
        $removeColumnsRevert = [];
        $addColumns = [];
        $addColumnsRevert = [];
        $columnAnnotations = [];

        foreach ($annotations as $annotation) {
            if (!$annotation instanceof Annotation\Column) {
                continue;
            }
            $columnAnnotations[$annotation->name] = $annotation;
            $column = $tableSchema->getColumn($annotation->name);
            if ($column) {
                //TODO: Check and generate alter information
            } else {
                $addColumns[] = $this->generateAddColumnStringFromAnnotation($reflectionClass, $annotation);
                $addColumnsRevert[] = $this->generateRemoveColumnString($reflectionClass, $annotation->name);
            }
        }

        foreach ($tableSchema->columns as $column) {
            if (!isset($columnAnnotations[$column->name])) {
                $removeColumns[] = $this->generateRemoveColumnString($reflectionClass, $column->name);
                $removeColumnsRevert[] = $this->generateAddColumnString($reflectionClass, $column);
            }
        }


        $safeUp = implode(PHP_EOL . $this->tabSpace, $addColumns);
        $safeUp .= PHP_EOL . $this->tabSpace . implode(PHP_EOL . $this->tabSpace, $removeColumns);

        $safeDown = implode(PHP_EOL . $this->tabSpace, $removeColumnsRevert);
        $safeDown .= PHP_EOL . $this->tabSpace . implode(PHP_EOL . $this->tabSpace, $addColumnsRevert);

        $fileContents = file_get_contents(__DIR__ . '/resources/template-alter.txt');
        $fileContents = str_replace([
            '__ClassNameFull__',
            '__ClassName__',
            '__safe_up__',
            '__safe_down__',
        ], [
            $reflectionClass->getName(),
            $this->getFileName($reflectionClass->getName()),
            $safeUp,
            $safeDown
        ], $fileContents);

        return $fileContents;
    }

    /**
     * @param Annotation\Column $annotation
     * @return string
     */
    protected function generateNewColumnString(Annotation\Column $annotation)
    {
        return $this->generateColumnDefinition(
            $annotation->type,
            $annotation->typeArgs,
            $annotation->notNull,
            $annotation->extra
        );
    }

    /***
     * @param \ReflectionClass $reflectionClass
     * @param $columnName
     * @return string
     */
    protected function generateRemoveColumnString(\ReflectionClass $reflectionClass, $columnName)
    {
        return sprintf('$this->dropColumn(%s, "%s");', $this->getTableName($reflectionClass), $columnName);
    }

    /**
     * @param $class
     * @return string
     */
    protected function getFileName($class)
    {
        $reflectionClass = $this->getReflectionClass($class);
        $classname = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $reflectionClass->getShortName()));
        $name = 'm' . gmdate('ymd_His') . '_' . $classname;
        return $name;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return string
     */
    protected function getTableName(\ReflectionClass $reflectionClass)
    {
        return $reflectionClass->getShortName() . '::tableName()';
    }

    /**
     * @param string $table
     * @param string $name
     * @param string $type
     * @param array $typeArgs
     * @param bool $notNull
     * @param string $extra
     * @return string
     */
    protected function getAddColumnString($table, $name, $type, array $typeArgs, $notNull = false, $extra = '')
    {
        $columnDefinition = $this->generateColumnDefinition($type, $typeArgs, $notNull, $extra);
        return sprintf('$this->addColumn(%s, "%s", %s);', $table, $name, $columnDefinition);
    }

    /**
     * @param $type
     * @param array $typeArgs
     * @param bool $notNull
     * @param string $extra
     * @return string
     */
    protected function generateColumnDefinition($type, array $typeArgs, $notNull = false, $extra = '')
    {
        $typeArgs = implode(',', $typeArgs);
        return sprintf(
            '$this->' . $type . '(%s)%s %s',
            $typeArgs,
            $notNull ? '->notNull()' : '',
            $extra ? ' . "' . $extra . '"' : ''
        );
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param ColumnSchema $column
     * @return string
     */
    protected function generateAddColumnString(\ReflectionClass $reflectionClass, ColumnSchema $column)
    {
        return $this->getAddColumnString(
            $this->getTableName($reflectionClass),
            $column->name,
            $this->getTypeFromColumn($column),
            [],
            !$column->allowNull
        );
    }

    /**
     * @param ColumnSchema $column
     * @return string
     */
    protected function getTypeFromColumn(ColumnSchema $column)
    {
        if (($column->type == 'smallint') && ($column->size == 1)) {
            return 'boolean';
        }
        return $column->type;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @param Annotation\Column $annotation
     * @return string
     */
    protected function generateAddColumnStringFromAnnotation(
        \ReflectionClass $reflectionClass,
        Annotation\Column $annotation
    ) {
        return $this->getAddColumnString(
            $this->getTableName($reflectionClass),
            $annotation->name,
            $annotation->type,
            $annotation->typeArgs,
            $annotation->notNull,
            $annotation->extra
        );
    }
}
