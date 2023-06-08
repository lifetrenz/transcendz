<?php

namespace Lifetrenz\Transcendz\Trait;

trait ResultRecordTrait
{
    /**
     * Get the value of resultDataRecordDTO
     */
    public function getResultDataRecordDTO(): mixed
    {
        return $this->resultDataRecordDTO;
    }

    /**
     * Set the value of resultDataRecordDTO
     *
     * @param mixed $resultDataRecordDTO
     */
    public function setResultDataRecordDTO($resultDataRecordDTO): void
    {
        $this->resultDataRecordDTO = $resultDataRecordDTO;
    }
}
