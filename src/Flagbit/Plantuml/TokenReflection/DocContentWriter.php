<?php

namespace Flagbit\Plantuml\TokenReflection;

use ReflectionClass;

class DocContentWriter extends \Flagbit\Plantuml\TokenReflection\WriterAbstract
{
    /**
     * @param ReflectionClass $class
     * @return string
     */
    public function writeProperties(ReflectionClass $class)
    {
        $written = '';
        $docComment = (string)$class->getDocComment();
        $matches = array();
        preg_match_all('/\*\h+@property(?:-read|-write|)\h+([^\h]+)\h+\$([^\s]+)\s/', (string)$docComment, $matches);
        foreach($matches[2] as $i => $name) {
            $written .= $this->writeProperty($name, $matches[1][$i]);
        }
        return $written;
    }

    /**
     * @param string $name
     * @param string $type
     * @return string
     */
    protected function writeProperty(string $name, string $type) {
        return $this->formatLine($this->writeVisibility() . $name
            . $this->writeType($type));
    }


    /**
     * @param string $type
     * @return string
     */
    public function writeType($type)
    {
        return ' : '.$type;
    }

    /**
     * Public by definition if in a docblock.
     * @return string
     */
    protected function writeVisibility() {
        return '+';
    }

    /**
     * @param ReflectionClass $class
     * @return string
     */
    public function writeMethods(ReflectionClass $class)
    {
        $written = '';
        $docComment = (string)$class->getDocComment();
        $matches = array();
        preg_match_all('/\*\h+@method\h+([^\h]+)\h+([^(\s]+)(?:\h*\(\h*([^)]*)\h*\))?\s/', (string)$docComment, $matches);
        foreach($matches[2] as $i => $name) {
            $written .= $this->writeMethod($name, $matches[3][$i], $matches[1][$i]);
        }
        return $written;
    }

    /**
     * @param string $name
     * @param string $params
     * @param string $returnType
     * @return string
     */
    protected function writeMethod(string $name, string $params, string $returnType)
    {
        return $this->formatLine($this->writeVisibility()
            . $name . $this->writeParameters($params)
            . $this->writeReturnType($returnType));
    }

    /**
     * @param string $returnType
     * @return string
     */
    protected function writeReturnType($returnType) {
        return $this->writeType($returnType);
    }


    /**
     * @param string $params
     * @return string
     */
    private function writeParameters($params)
    {
        return '(' . $params . ')';
    }

}
