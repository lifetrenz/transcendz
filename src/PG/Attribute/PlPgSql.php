<?php

namespace Lifetrenz\Transcendz\PG\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class PlPgSql
{
    public function __construct(
        private string $name,
        private string $schema,
        private string $dbIdentifier
    ) {
    }

    /**
     * Get the value of functionName.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the value of schema.
     */
    public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * Get the value of dbIdentifier.
     */
    public function getDbIdentifier(): string
    {
        return $this->dbIdentifier;
    }
}
