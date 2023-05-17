<?php

namespace Lifetrenz\Transcendz\PG;

interface PlPgSqlDataSetFunction
{
    public function setResultDataSetDTO(?array $resultDTO): void;
    public function getResultDataSetDTO(): ?array;
}
