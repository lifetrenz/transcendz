<?php

namespace Lifetrenz\Transcendz\PG;

interface PlPgSqlDataRecordFunction
{
    public function setResultDataRecordDTO(mixed $resultDataRecordDTO): void;
    public function getResultDataRecordDTO(): mixed;
}
