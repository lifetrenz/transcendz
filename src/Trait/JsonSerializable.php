<?php

namespace Lifetrenz\Transcendz\Trait;

use DateTime;
use Lifetrenz\Transcendz\Attribute\HiddenField;
use ReflectionClass;

trait JsonSerializable
{
    public function jsonSerialize(): mixed
    {
        $ref = new ReflectionClass($this);
        $props = $ref->getProperties();
        $HiddenProps = [];
        foreach ($props as $prop) {
            $attrs = $prop->getAttributes(HiddenField::class);
            if (!empty($attrs)) {
                $HiddenProps[] = $prop->getName();
            }
        }

        $resultSet = array_diff_key(get_object_vars($this), array_flip($HiddenProps));

        return array_map(
            fn ($var) => ($var instanceof DateTime) ?
                $var->getTimestamp() :
                $var,
            $resultSet
        );
    }
}
