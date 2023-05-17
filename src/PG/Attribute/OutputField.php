<?php

namespace Lifetrenz\Transcendz\PG\Attribute;

use Attribute;
use Lifetrenz\Transcendz\PG\DataType;

#[Attribute(Attribute::TARGET_PROPERTY)]
class OutputField
{
    public function __construct(
        private string $name,
        private DataType $type
    ) {
    }

    /**
     * Get the value of name
     *
     * @return  mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of type
     *
     * @return  mixed
     */
    public function getType()
    {
        return $this->type;
    }
}
