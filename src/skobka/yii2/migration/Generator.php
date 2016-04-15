<?php

namespace skobka\yii2\migration;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use skobka\yii2\annotation as Annotation;

class Generator
{
    /**
     * @var AnnotationReader
     */
    protected $reader;

    protected $ignoreAnnotations = ['website', 'project'];

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
        foreach($names as $name){
            AnnotationReader::addGlobalIgnoredName($name);
        }
    }

    /**
     * Generate migration from annotations
     * @param string $class
     * @param string $targetDir
     */
    public function generate($class, $targetDir)
    {
        $reflectionClass = $this->getReflectionClass($class);
        $annotations = $this->getNestedAnnotations($reflectionClass);
        $fileContents = $this->generateFileContents($reflectionClass, $annotations);
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
                $annotations = array_merge_recursive($annotations, $this->getNestedAnnotations($parent, $superclassAnnotation->active));
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
                if(!$annotation->name){
                    throw new Annotation\exceptions\UndefinedTableNameException;
                }                
                $tableAnnotationCount++;
            } else if ($annotation instanceof Annotation\Column){
                if(!$annotation->name){
                    throw new Annotation\exceptions\UndefinedColumnNameException;
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
     * @return string
     */
    protected function generateFileContents(\ReflectionClass $reflectionClass, $annotations)
    {
        $table = null;
        $columns = [];
        foreach ($annotations as $annotation) {
            if (($annotation instanceof Annotation\Table) && !$table) {
                $table = $annotation->name;
            } elseif ($annotation instanceof Annotation\Column) {
                $typeArgs = implode(',', $annotation->typeArgs);
                $columns[] = sprintf(
                    '"%s" => $this->' . $annotation->type . '(%s)%s . "%s"',
                    $annotation->name,
                    $annotation->typeArgs ? $typeArgs : '',
                    $annotation->notNull ? '->notNull()' : '',
                    $annotation->extra
                );
            }
        }
        $fileContents = file_get_contents(__DIR__ . '/resources/template.txt');
        $fileContents = str_replace([
            '__ClassName__',
            '__TableName__',
            '__Columns__',
        ], [
            $this->getFileName($reflectionClass),
            $reflectionClass->getShortName(),
            implode(',' . PHP_EOL . '            ', $columns),
        ], $fileContents);
        return $fileContents;
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
}