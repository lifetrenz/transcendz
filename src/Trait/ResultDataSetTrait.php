<?php

namespace Lifetrenz\Transcendz\Trait;

trait ResultDataSetTrait
{
    /**
     * Get the value of resultDataSetDTO
     */
    public function getResultDataSetDTO(): ?array
    {
        return $this->resultDataSetDTO;
    }

    /**
     * Set the value of resultDataSetDTO
     *
     * @param array $resultDataSetDTO
     */
    public function setResultDataSetDTO(?array $resultDataSetDTO): void
    {
        $this->resultDataSetDTO = $resultDataSetDTO;
    }
}
