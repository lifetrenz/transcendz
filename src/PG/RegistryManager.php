<?php

namespace Lifetrenz\Transcendz\PG;

class RegistryManager
{
    public function __construct(
        private ConnectionRegistry $registry
    ) {
    }

    public function execute(
        PlPgSqlDataSetFunction | PlPgSqlDataRecordFunction | PlPgSqlScalarDataFunction $plPgSqlFunction
    ) {
        $plPgSqlFunctionManager = new PlPgSqlFunctionManager($this->registry);
        return $plPgSqlFunctionManager->execute($plPgSqlFunction);
    }
}
