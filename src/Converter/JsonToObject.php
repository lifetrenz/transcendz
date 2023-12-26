<?php

namespace Lifetrenz\Transcendz\Converter;

use DateTime;
use Exception;
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
            $arrayElementType = null;

            if ($jsonPropertyAttribute === null) {
                $propertyName = $paramProperty->getName();
            } else {
                $jsonProperty = $jsonPropertyAttribute->newInstance();
                $propertyName = $jsonProperty->getName();
                $valueRequired = $jsonProperty->isRequired();
                $defaultValue = $jsonProperty->getDefault();
                $format = $jsonProperty->getFormat();
                $arrayElementType = $jsonProperty->getElementType();
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

            if ($paramProperty->getType()->getName() === DateTime::class && $value !== null) {
                if ($format === "EPOCH") {
                    $value = "@$value";
                }
                try {
                    $value = new DateTime($value);
                } catch (Exception $ex) {
                    throw new InvalidInputArgument(
                        sprintf(
                            "Failed %s to convert to DateTime",
                            $propertyName
                        )
                    );
                }
            } elseif ($paramProperty->getType()->getName() === "array" && $value !== null) {
                    $arrayValue = array_map(
                        fn ($eachElement) =>
                            is_scalar($eachElement) || $eachElement === null ? 
                                $eachElement : 
                                self::convert(
                                    json_encode($eachElement), 
                                    $arrayElementType
                                ),
                        $value
                    );
                    $value = $arrayValue;
            } elseif ($paramProperty !== null && !$paramProperty->getType()->isBuiltin()) {
                $value = self::convert(json_encode($value), $paramProperty->getType()->getName());
            }
            $paramProperty->setValue($paramObject, $value);
        }

        return $paramObject;
    }
}
