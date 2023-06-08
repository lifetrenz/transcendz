<?php

namespace Lifetrenz\Transcendz\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsonProperty
{
    public function __construct(
        private string $name,
        private bool $required = false,
        private mixed $default = null,
        private ?string $format = null
    ) {
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of required
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Get the value of default
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Get the value of format
     */
    public function getFormat()
    {
        return $this->format;
    }
}
