<?php

namespace Lifetrenz\Transcendz\PG\Attribute;

use Attribute;
use Lifetrenz\Transcendz\PG\DataType;

#[Attribute(Attribute::TARGET_PROPERTY)]
class InputArgument
{
    public function __construct(
        private string $name,
        private DataType $type
    ) {
    }

    /**
     * Get the value of name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the value of type.
     */
    public function getType(): DataType
    {
        return $this->type;
    }
}
