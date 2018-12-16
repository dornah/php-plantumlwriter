<?php

namespace Flagbit\Plantuml\TokenReflection;

use ReflectionMethod;
use ReflectionParameter;

class MethodWriter extends \Flagbit\Plantuml\TokenReflection\WriterAbstract
{
    /**
     * @param ReflectionMethod $method
     * @return string
     */
    public function writeElement(ReflectionMethod $method)
    {
        return $this->formatLine($this->writeVisibility($method)
            . ($method->isStatic() ? '{static}' : '') . $method->getName()
            . $this->writeParameters($method) . $this->writeReturnType($method));
    }

    /**
     * @param ReflectionMethod[] $methods
     * @return string
     */
    public function writeElements(array $methods)
    {
        // see https://bugs.php.net/bug.php?id=50688
        @usort($methods, function(ReflectionMethod $a, ReflectionMethod $b) {
            return strnatcasecmp($a->getName(), $b->getName());
        });

        $methodsString = '';
        foreach ($methods as $method) {
            $methodsString .= $this->writeElement($method);
        }
        return $methodsString;
    }

    /**
     * @param ReflectionMethod $method
     * @return string
     */
    private function writeVisibility(ReflectionMethod $method)
    {
        return $method->isPrivate() ? '-' : ($method->isProtected() ? '#' : '+');
    }

    /**
     * @param ReflectionMethod $method
     * @return string
     */
    private function writeParameters(ReflectionMethod $method)
    {
        $parameters = array();
        foreach ($method->getParameters() as $parameter) {
            $parameters[] = $this->writeParameter($method, $parameter);
        }
        return '(' . implode(', ' , $parameters) . ')';
    }

    /**
     * @param ReflectionMethod $method
     * @param ReflectionParameter $parameter
     * @return string
     */
    private function writeParameter(ReflectionMethod $method, ReflectionParameter $parameter)
    {
        $parameterString = $parameter->getName();

        if ($parameter->getName()) {
            $parameterString .= ' : ' . $this->formatClassName($parameter->getName());
        }
        else {
            preg_match('/\*\h+@param\h+([^\h]+)\h+\$' . preg_quote($parameterString). '\s/', (string) $method->getDocComment(), $matches);
            if (isset($matches[1])) {
                $parameterString .= ' : ' . $this->formatClassName($matches[1]);
            }
        }

        if ($parameter->isOptional() && $parameter->isDefaultValueAvailable()) {
            $parameterString .= ' = ' . $this->formatValue($parameter->getDefaultValue());
        }

        return $parameterString;
    }

    /**
     * @param ReflectionMethod $method
     * @return string
     */
    private function writeReturnType(ReflectionMethod $method)
    {
        $returnType = '';
        preg_match('/\*\h+@return\h+([^\h]+)/', (string) $method->getDocComment(), $matches);
        if (isset($matches[1])) {
            $returnType = ' : ' . $this->formatClassName($matches[1]);
        }
        return $returnType;
    }
}
