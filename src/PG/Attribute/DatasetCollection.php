<?php

namespace Lifetrenz\Transcendz\PG\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DatasetCollection
{
    public function __construct(
        private string $dtoClassName
    ) {
    }

    /**
     * Get the value of dtoClassName
     *
     * @return  string
     */
    public function getDtoClassName(): string
    {
        return $this->dtoClassName;
    }
}
