<?php

namespace Lifetrenz\Transcendz\PG;

class PlPgSqlFunctionManager
{
    public function __construct(
        private ConnectionRegistry $connectionRegistry
    ) {
    }

    public function execute(
        PlPgSqlDataSetFunction |
        PlPgSqlDataRecordFunction |
        PlPgSqlScalarDataFunction
        $plPgSqlFunction
    ) {
        $functionMap = new PlPgSqlFunctionMap($plPgSqlFunction, $this->connectionRegistry);
        $queryExecutor = new QueryExecutor(
            $functionMap->getConnection(),
            $functionMap->getQuery()
        );
        $result = $queryExecutor->execute();

        if ($plPgSqlFunction instanceof PlPgSqlDataSetFunction) {
            if ($result === null) {
                $plPgSqlFunction->setResultDataSetDTO(null);
            } else {
                $plPgSqlFunction->setResultDataSetDTO($functionMap->mapResultSet($result));
            }
        } elseif ($plPgSqlFunction instanceof PlPgSqlDataRecordFunction) {
            if ($result === null) {
                $plPgSqlFunction->setResultDataRecordDTO(null);
            } else {
                $mappedResult = $functionMap->mapResultSet($result);
                $plPgSqlFunction->setResultDataRecordDTO($mappedResult[0]);
            }
        } elseif ($plPgSqlFunction instanceof PlPgSqlScalarDataFunction) {
            if ($result === null) {
                $plPgSqlFunction->setResult(null);
            } else {
                $plPgSqlFunction->setResult($functionMap->getScalarResult($result));
            }
        }
    }
}
