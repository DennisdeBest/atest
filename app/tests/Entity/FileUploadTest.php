<?php

namespace App\Tests\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadTest extends ApiTestCase
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
                    'requestedOutputFormat' => 'json',
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
                    'requestedOutputFormat' => 'json',
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
}
