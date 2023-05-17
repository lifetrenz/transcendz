<?php

namespace Lifetrenz\Transcendz\PG;

interface PlPgSqlDataRecordFunction
{
    public function setResultDataRecordDTO(mixed $resultDTO): void;
    public function getResultDataRecordDTO(): mixed;
}
