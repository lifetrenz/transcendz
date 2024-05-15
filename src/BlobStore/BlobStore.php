<?php

namespace Lifetrenz\Transcendz\BlobStore;

interface BlobStore
{
    public function save(string $filePath, string $content): bool;
    public function fetch(string $filePath): ?string;
    public function delete(string $filePath): bool;
}
