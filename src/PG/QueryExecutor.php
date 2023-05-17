<?php

namespace Lifetrenz\Transcendz\PG;

use Exception;
use Lifetrenz\Transcendz\Exception\DbExecutionException;

class QueryExecutor
{
    public function __construct(
        private Connection $connection,
        private string $query
    ) {
    }

    public function execute()
    {
        $connection = $this->connection->connect();
        try {
            $result = pg_query($connection, $this->query);
            if (!$result) {
                throw new DbExecutionException(
                    sprintf(
                        "Error in Executing DB function! \n Statement: %s \n Error: %s",
                        $this->query,
                        pg_last_error($connection)
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
        } catch (Exception $ex) {
            throw new Exception(
                sprintf(
                    "Error in Executing DB function! \n Statement: %s \n Error: %s",
                    $this->query,
                    pg_last_error($connection)
                )
            );
        }
    }
}
