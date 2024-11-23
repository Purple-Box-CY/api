<?php

namespace App\Service;

use App\Service\Infrastructure\LogService;
use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3Service
{
    public function __construct(
        private string     $s3Bucket,
        private string     $s3BackupBucket,
        private string     $cdnDomain,
        private S3Client   $s3Client,
        private LogService $logger,
    ) {
    }

    public function deleteFileFromCdn(?string $filePath)
    {
        if (!$filePath) {
            return;
        }
        try {
            $oldFileParams = parse_url($filePath);
            if (is_array($oldFileParams) && isset($oldFileParams['path'])) {
                $key = substr($oldFileParams['path'], 1);
                $this->s3Client->deleteMatchingObjects($this->s3Bucket, $key);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Failed to delete image from aws',
                [
                    'error'  => $e->getMessage(),
                    'method' => __METHOD__,
                ]);
        }
    }

    public function uploadFileToCDN(
        UploadedFile|File $file,
        string            $newFilePath,
        array             $options = [],
    ): string {
        $body = $file->getContent();
        try {
            $resultUpload = $this->s3Client->upload(
                bucket: $this->s3Bucket,
                key: $newFilePath,
                body: $body,
                options: $options,
            );
        } catch (\Throwable $e) {
            $this->logger->error('Failed to upload file to main cdn',
                [
                    'error' => $e->getMessage(),
                    'key'   => $newFilePath,
                ]);
        }
        if ($this->s3BackupBucket) {
            try {
                $this->s3Client->upload(
                    bucket: $this->s3BackupBucket,
                    key: $newFilePath,
                    body: $body,
                    options: $options,
                );
            } catch (\Throwable $e) {
                $this->logger->error('Failed to upload file to reserve cdn',
                    [
                        'error' => $e->getMessage(),
                        'key'   => $newFilePath,
                    ]);
            }
        }

        return sprintf('%s/%s', $this->cdnDomain, $newFilePath);
    }

    public function uploadContentToCDN(
        string $fileContent,
        string $newFilePath,
        bool   $isImage = false,
    ): string {
        $options = [];
        if ($isImage) {
            $options = [
                'params' => [
                    'ContentType' => 'image/jpg',
                ],
            ];
        }
        $this->s3Client->upload(
            bucket: $this->s3Bucket,
            key: $newFilePath,
            body: $fileContent,
            options: $options,
        );

        if ($this->s3BackupBucket) {
            $this->s3Client->upload(
                bucket: $this->s3Bucket,
                key: $newFilePath,
                body: $fileContent,
                options: $options,
            );
        }

        return sprintf('%s/%s', $this->cdnDomain, $newFilePath);
    }
}