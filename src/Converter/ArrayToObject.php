<?php

namespace Lifetrenz\Transcendz\Converter;

use DateTime;
use Lifetrenz\Transcendz\Attribute\ArrayKey;
use Lifetrenz\Transcendz\Exception\InvalidInputArgument;
use ReflectionClass;

class ArrayToObject
{
    public static function convert(array $array, string $className)
    {
        $paramReflection = new ReflectionClass($className);
        $paramObject = $paramReflection->newInstanceWithoutConstructor();
        foreach ($paramReflection->getProperties() as $paramProperty) {
            $arrayKeyAttribute = $paramProperty->getAttributes(ArrayKey::class)[0] ?? null;
            $propertyName = null;
            $valueRequired = false;
            $defaultValue = null;

            if ($arrayKeyAttribute === null) {
                $propertyName = $paramProperty->getName();
            } else {
                $arrayKey = $arrayKeyAttribute->newInstance();
                $propertyName = $arrayKey->getName();
                $valueRequired = $arrayKey->isRequired();
                $defaultValue = $arrayKey->getDefault();
                $format = $arrayKey->getFormat();
            }

            if ($valueRequired && $defaultValue === null && ($array[$propertyName] ?? null) === null) {
                throw new InvalidInputArgument(
                    sprintf(
                        "Required %s key is not found in the array list",
                        $propertyName
                    )
                );
            }
            $value = $array[$propertyName] ?? $defaultValue;

            if ($paramProperty->getType()->getName() === DateTime::class && $value !== null) {
                if ($format === "EPOCH") {
                    $value = "@$value";
                }
                $value = new DateTime($value);
            } elseif ($paramProperty !== null && !$paramProperty->getType()->isBuiltin() && $value !== null) {
                $value = self::convert($value, $paramProperty->getType()->getName());
            }

            $paramProperty->setValue($paramObject, $value);
        }

        return $paramObject;
    }
}
