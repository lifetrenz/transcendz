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

        return array_merge(
            ...array_map(
                function ($key, $var) use ($jsonFormats) {
                    if ($var instanceof DateTime) {
                        if (array_key_exists($key, $jsonFormats)) {
                            return [$key => $var->format($jsonFormats[$key])];
                        }

                        return [$key => $var->getTimestamp()];
                    }
                    return [$key => $var];
                },
                array_keys($resultSet),
                array_values($resultSet)
            )
        );
    }
}
