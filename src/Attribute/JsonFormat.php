<?php

namespace Lifetrenz\Transcendz\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonFormat
{
    public function __construct(
        private string $format
    ) {
    }

    /**
     * Get the value of format
     */
    public function getFormat()
    {
        return $this->format;
    }
}
