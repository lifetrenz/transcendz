<?php

namespace Lifetrenz\Transcendz\Converter;

use DateTime;
use Lifetrenz\Transcendz\Attribute\JsonProperty;
use Lifetrenz\Transcendz\Exception\InvalidInputArgument;
use ReflectionClass;

class JsonToObject
{
    public static function convert(string $json, string $className)
    {
        $jsonObject = json_decode($json);
        $paramReflection = new ReflectionClass($className);
        $paramObject = $paramReflection->newInstanceWithoutConstructor();
        foreach ($paramReflection->getProperties() as $paramProperty) {
            $jsonPropertyAttribute = $paramProperty->getAttributes(JsonProperty::class)[0] ?? null;
            $propertyName = null;
            $valueRequired = false;
            $defaultValue = null;

            if ($jsonPropertyAttribute === null) {
                $propertyName = $paramProperty->getName();
            } else {
                $jsonProperty = $jsonPropertyAttribute->newInstance();
                $propertyName = $jsonProperty->getName();
                $valueRequired = $jsonProperty->isRequired();
                $defaultValue = $jsonProperty->getDefault();
                $format = $jsonProperty->getFormat();
            }

            if ($valueRequired && $defaultValue === null && $jsonObject->$propertyName === null) {
                throw new InvalidInputArgument(
                    sprintf(
                        "Required %s property is not found in the JSON string",
                        $propertyName
                    )
                );
            }
            $value = $jsonObject->$propertyName ?? $defaultValue;

            if ($paramProperty->getType() === DateTime::class && $value !== null) {
                $value = new DateTime($value);
            }

            $paramProperty->setValue($paramObject, $value);
        }

        return $paramObject;
    }
}
