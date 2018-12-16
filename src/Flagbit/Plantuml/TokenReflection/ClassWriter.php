<?php

namespace Flagbit\Plantuml\TokenReflection;

use ReflectionClass;

class ClassWriter extends WriterAbstract
{
    /**
     * @var \Flagbit\Plantuml\TokenReflection\ConstantWriter
     */
    private $constantWriter;

    /**
     * @var \Flagbit\Plantuml\TokenReflection\PropertyWriter
     */
    private $propertyWriter;

    /**
     * @var \Flagbit\Plantuml\TokenReflection\MethodWriter
     */
    private $methodWriter;

    /**
     * @var \Flagbit\Plantuml\TokenReflection\DocContentWriter
     */
    private $docContentWriter;

    public function __construct()
    {
        $this->setIndent('');
    }

    /**
     * @param \Flagbit\Plantuml\TokenReflection\ConstantWriter $constantWriter
     */
    public function setConstantWriter($constantWriter)
    {
        $this->constantWriter = $constantWriter;
    }

    /**
     * @param \Flagbit\Plantuml\TokenReflection\MethodWriter $methodWriter
     */
    public function setMethodWriter($methodWriter)
    {
        $this->methodWriter = $methodWriter;
    }

    /**
     * @param \Flagbit\Plantuml\TokenReflection\PropertyWriter $propertyWriter
     */
    public function setPropertyWriter($propertyWriter)
    {
        $this->propertyWriter = $propertyWriter;
    }

    /**
     * @param \Flagbit\Plantuml\TokenReflection\DocContentWriter $docContentWriter
     */
    public function setDocContentWriter($docContentWriter)
    {
        $this->docContentWriter = $docContentWriter;
    }

    /**
     * @param ReflectionClass $class
     *
     * @return string
     */
    public function writeElement(ReflectionClass $class)
    {
        $classString = $this->formatLine(
            $this->writeAbstract($class) . $this->writeObjectType($class) . ' ' . $this->formatClassName(
                $class->getName()
            ) . ' {'
        );

        if ($this->constantWriter) {
            /**
             * @todo Investigate the issue with constants
             */
            //$classString .= $this->constantWriter->writeElements($class->getReflectionConstants());
        }

        if ($this->propertyWriter) {
            $classString .= $this->propertyWriter->writeElements($class->getProperties());
            if($this->docContentWriter) {
                $classString .= $this->docContentWriter->writeProperties($class);
            }
        }

        if ($this->methodWriter) {
            $classString .= $this->methodWriter->writeElements($class->getMethods());
            if($this->docContentWriter) {
                $classString .= $this->docContentWriter->writeMethods($class);
            }
        }

        $classString .= $this->formatLine('}');

        if ($class->getParentClass()) {
            $classString .= $this->formatLine(
                $this->writeObjectType($class) . ' ' . $this->formatClassName($class->getName()) . ' extends '
                . $this->formatClassName(
                    $class->getParentClass()->getName()
                )
            );
        }

        if ($interfaceNames = $class->getInterfaceNames()) {
            foreach ($interfaceNames as $interfaceName) {
                $classString .= $this->formatLine(
                    $this->writeObjectType($class) . ' ' . $this->formatClassName($class->getName()) . ' implements '
                    . $this->formatClassName(
                        $interfaceName
                    )
                );
            }
        }

        return $classString;
    }

    /**
     * @param ReflectionClass $class
     *
     * @return string
     */
    private function writeAbstract(ReflectionClass $class)
    {
        $return = '';
        if (true === $class->isAbstract() && false === $class->isInterface()) {
            $return = 'abstract ';
        }
        return $return;
    }

    /**
     * @param ReflectionClass $class
     *
     * @return string
     */
    private function writeObjectType(ReflectionClass $class)
    {
        $return = 'class';
        if (true === $class->isInterface()) {
            $return = 'interface';
        }
        return $return;
    }
}
