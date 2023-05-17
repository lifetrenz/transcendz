<?php

namespace Lifetrenz\Transcendz\Trait;

trait ResultRecordTrait
{
    /**
     * Get the value of resultDataRecordDTO
     */
    public function getResultDataRecordDTO()
    {
        return $this->resultDataRecordDTO;
    }

    /**
     * Set the value of resultDataRecordDTO
     *
     * @param mixed $resultDataRecordDTO
     */
    public function setResultDataRecordDTO($resultDataRecordDTO)
    {
        $this->resultDataRecordDTO = $resultDataRecordDTO;
    }
}
