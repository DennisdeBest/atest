<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private function setupTestfile(string $filename): string
    {
        $path = __DIR__.'/../fixtures/'.$filename;

        $tmpPath = tempnam(sys_get_temp_dir(), 'upload_test_');
        copy($path, $tmpPath);

        return $tmpPath;
    }

    public function testValidCsvUploadIsAccepted(): void
    {
        $client = static::createClient();

        $tmpPath = $this->setupTestfile('valid.csv');

        $response = $client->request('POST', '/api/files', [
            'headers' => [
                'accept' => 'application/ld+json',
                'Content-Type' => 'multipart/form-data',
            ],
            'extra' => [
                'parameters' => [
                    'outputFormat' => 'json',
                ],
                'files' => [
                    'file' => new UploadedFile(
                        $tmpPath,
                        'valid.csv',
                        'text/csv',
                        test: true,
                    ),
                ],
            ],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains([
            '@type' => 'File',
        ]);
    }

    public function testHtmlUploadIsRejected(): void
    {
        $client = static::createClient();

        $response = $client->request('POST', '/api/files', [
            'headers' => [
                'accept' => 'application/ld+json',
                'Content-Type' => 'multipart/form-data',
            ],
            'extra' => [
                'parameters' => [
                    'outputFormat' => 'json',
                ],
                'files' => [
                    'file' => new UploadedFile(
                        __DIR__.'/../fixtures/invalid.html',
                        'invalid.html',
                        'text/html',
                        test: true,
                    ),
                ],
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertJsonContains([
            'violations' => [
                ['propertyPath' => 'file'],
            ],
        ]);
    }

    public function testDownloadReturnsConvertedFile(): void
    {
        $client = static::createClient();

        // 1. upload a valid CSV
        $tmpPath = $this->setupTestfile('valid.csv');

        $uploadResponse = $client->request('POST', '/api/files', [
            'headers' => [
                'accept' => 'application/ld+json',
                'Content-Type' => 'multipart/form-data',
            ],
            'extra' => [
                'parameters' => [
                    'outputFormat' => 'json',
                ],
                'files' => [
                    'file' => new UploadedFile(
                        $tmpPath,
                        'valid.csv',
                        'text/csv',
                        test: true,
                    ),
                ],
            ],
        ]);

        self::assertResponseStatusCodeSame(201);

        $data = $uploadResponse->toArray();
        self::assertArrayHasKey('uid', $data);

        $uid = $data['uid'];

        // 2. download the converted file
        $downloadResponse = $client->request('GET', sprintf('/api/files/%s/download', $uid));

        self::assertResponseIsSuccessful(); // 2xx
        self::assertSame(200, $downloadResponse->getStatusCode());

        $headers = $downloadResponse->getHeaders(false);

        self::assertArrayHasKey('content-disposition', $headers);
        self::assertStringContainsString('attachment', $headers['content-disposition'][0]);

        self::assertArrayHasKey('content-type', $headers);
        self::assertStringContainsString('application/json', $headers['content-type'][0]);
    }
}
