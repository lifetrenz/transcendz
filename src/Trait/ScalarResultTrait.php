<?php

namespace Lifetrenz\Transcendz\Trait;

trait ScalarResultTrait
{
    /**
     * Get the value of result
     */
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * Set the value of resultDataRecordDTO
     *
     * @param mixed $result
     */
    public function setResult($result): void
    {
        $this->result = $result;
    }
}
