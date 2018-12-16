<?php

namespace Flagbit\Plantuml\TokenReflection;

use ReflectionProperty;

class PropertyWriter extends WriterAbstract
{
    /**
     * @param ReflectionProperty $property
     *
     * @return string
     */
    public function writeElement(ReflectionProperty $property)
    {
        return $this->formatLine($this->writeVisibility($property) . $property->getName()
            . $this->writeType($property) . $this->writeValue($property));
    }

    /**
     * @param ReflectionProperty[] $properties
     *
     * @return string
     */
    public function writeElements(array $properties)
    {
        // see https://bugs.php.net/bug.php?id=50688
        @usort($properties, function(ReflectionProperty $a, ReflectionProperty $b) {
            return strnatcasecmp($a->getName(), $b->getName());
        });

        $propertiesString = '';
        foreach ($properties as $property) {
            /** @var $property ReflectionProperty */
            $propertiesString .= $this->writeElement($property);
        }
        return $propertiesString;
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return string
     */
    public function writeVisibility(ReflectionProperty $property)
    {
        return $property->isPrivate() ? '-' : ($property->isProtected() ? '#' : '+');
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return string
     */
    public function writeType(ReflectionProperty $property)
    {
        $type = '';
        preg_match('/\*\h+@var\h+([^\h]+)/', (string) $property->getDocComment(), $matches);
        if (isset($matches[1])) {
            $type = ' : ' . $this->formatClassName($matches[1]);
        }
        return $type;
    }

    /**
     * @param ReflectionProperty $property
     *
     * @return string
     */
    public function writeValue(ReflectionProperty $property)
    {
        $value = '';
        if ($property->getDeclaringClass() && $defaultProperties = $property->getDeclaringClass()->getDefaultProperties()) {
            if (!is_null($defaultProperties[$property->getName()])) {
               $value = ' = ' . $this->formatValue($defaultProperties[$property->getName()]);
            }
        }
        return $value;
    }
}
