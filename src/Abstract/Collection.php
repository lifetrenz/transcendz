<?php

namespace Lifetrenz\Transcendz\Abstract;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;

class Collection implements Countable, Iterator, ArrayAccess, JsonSerializable
{
    private array $values = [];
    private int $position = 0;

    public function count(): int
    {
        return count($this->values);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function current(): mixed
    {
        return $this->values[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }

    public function valid(): bool
    {
        return isset($this->values[$this->position]);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->values[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->values[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->values[$offset]);
    }

    public function jsonSerialize(): array
    {
        return isset($this->values) ? array_values($this->values) : [];
    }

    /**
     * Get the value of values
     */
    public function getValues()
    {
        return $this->values;
    }
}
