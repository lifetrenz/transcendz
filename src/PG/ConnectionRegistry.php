<?php

namespace Lifetrenz\Transcendz\PG;

use Lifetrenz\Transcendz\Exception\InvalidPlPgSqlClass;

class ConnectionRegistry
{
    public function __construct(
        private array $connections
    ) {
    }

    /**
     * Get the value of connections
     */
    public function getConnection(string $connectionIdentifier)
    {
        if (!array_key_exists($connectionIdentifier, $this->connections)) {
            throw new InvalidPlPgSqlClass(sprintf(
                "No Connection exists for identifier %s.",
                $connectionIdentifier
            ));
        }
        return $this->connections[$connectionIdentifier];
    }
}
