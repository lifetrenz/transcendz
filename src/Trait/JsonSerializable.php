<?php

namespace Lifetrenz\Transcendz\Trait;

use DateTime;
use Lifetrenz\Transcendz\Attribute\HiddenField;
use Lifetrenz\Transcendz\Attribute\JsonFormat;
use ReflectionClass;

trait JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        $ref = new ReflectionClass($this);
        $props = $ref->getProperties();
        $hiddenProps = [];
        $jsonFormats = [];
        foreach ($props as $prop) {
            $hiddenAttribute = $prop->getAttributes(HiddenField::class);
            if (!empty($hiddenAttribute)) {
                $hiddenProps[] = $prop->getName();
            }
            $formatAttribute = $prop->getAttributes(JsonFormat::class);
            if (!empty($formatAttribute)) {
                $jsonFormats = [
                    ...$jsonFormats,
                    $prop->getName() => $formatAttribute[0]->newInstance()->getFormat()
                ];
            }
        }

        $resultSet = array_diff_key(get_object_vars($this), array_flip($hiddenProps));

        return array_map(
            fn ($key, $var) => ($var instanceof DateTime) ?
                (
                    !array_key_exists($key, $jsonFormats) ?
                    $var->getTimestamp() :
                    $var->format($jsonFormats[$key])
                ) :
                $var,
            array_keys($resultSet),
            array_values($resultSet)
        );
    }
}
