<?php

namespace Flagbit\Plantuml\TokenReflection;

use ReflectionClassConstant;

class ConstantWriter extends WriterAbstract
{
    /**
     * @param ReflectionClassConstant $constant
     *
     * @return string
     */
    public function writeElement(ReflectionClassConstant $constant)
    {
        return $this->formatLine('+{static}' . $constant->getName() . ' = ' . $this->formatValue($constant->getValue()));
    }

    /**
     * @param ReflectionClassConstant[] $constants
     *
     * @return string
     */
    public function writeElements(array $constants)
    {
        // see https://bugs.php.net/bug.php?id=50688
        @usort($constants, function(ReflectionClassConstant $a, ReflectionClassConstant $b) {
            return strnatcasecmp($a->getName(), $b->getName());
        });

        $constantsString = '';
        foreach ($constants as $constant) {
            /** @var $property ReflectionClassConstant */
            $constantsString .= $this->writeElement($constant);
        }
        return $constantsString;
    }
}
