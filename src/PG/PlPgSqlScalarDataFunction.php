<?php

namespace Lifetrenz\Transcendz\PG;

interface PlPgSqlScalarDataFunction
{
    public function setResult(mixed $result): void;
    public function getResult(): mixed;
}
