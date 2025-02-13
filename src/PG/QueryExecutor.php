<?php

namespace Lifetrenz\Transcendz\PG;

use Lifetrenz\Transcendz\Exception\DbConflictWithRecoveryException;
use Lifetrenz\Transcendz\Exception\DbExecutionException;

class QueryExecutor
{
    public function __construct(
        private Connection $connection,
        private string $query,
        private ?string $setOptions = null
    ) {
    }

    public function execute()
    {
        $connection = $this->connection->connect();
        if ($this->setOptions !== null) {
            pg_query($connection, $this->setOptions);
        }
        $result = pg_query($connection, $this->query);
        if (!$result) {
            $error = pg_last_error($connection);
            if (strpos($error, "canceling statement due to conflict with recovery") !== false) {
                throw new DbConflictWithRecoveryException(
                    sprintf(
                        "Error in Executing DB function! \n Statement: %s \n Error: %s",
                        $this->query,
                        $error
                    )
                );
            }
            throw new DbExecutionException(
                sprintf(
                    "Error in Executing DB function! \n Statement: %s \n Error: %s",
                    $this->query,
                    $error
                )
            );
        }
        return match (pg_num_rows($result)) {
            -1 => throw new DbExecutionException(sprintf(
                "Error in Executing DB function! \n Statement: %s \n Error: %s",
                $this->query,
                pg_last_error($connection)
            )),
            0 => null,
            default => pg_fetch_all($result)
        };
    }
}
