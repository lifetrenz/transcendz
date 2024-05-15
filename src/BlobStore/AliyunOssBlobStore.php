<?php

namespace Lifetrenz\Transcendz\BlobStore;

use Lifetrenz\Transcendz\Exception\BlobStoreException;
use OSS\Core\OssException;
use OSS\OssClient;

class AliyunOssBlobStore implements BlobStore
{
    public function __construct(
        private OssClient $ossClient,
        private string $bucket
    ) {
    }

    public function save(string $filePath, string $contnet): bool
    {
        $result = $this->ossClient->putObject($this->bucket, $filePath, $contnet);
        return $result["info"]["http_code"] === 200;
    }

    public function fetch(string $key): ?string
    {
        try {
            $object = $this->ossClient->getObject($this->bucket, $key);
            return $object["body"];
        } catch (OssException $e) {
            throw new BlobStoreException($e->getErrorMessage(), $e->getErrorCode());
        }
    }

    public function delete(string $key): bool
    {
        try {
            $this->ossClient->deleteObject($this->bucket, $key);
            return true;
        } catch (OssException $e) {
            return false;
        }
    }
}
