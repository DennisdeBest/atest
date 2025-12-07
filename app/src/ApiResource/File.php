<?php

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use ApiPlatform\OpenApi\Model\RequestBody as OpenApiRequestBody;
use ApiPlatform\OpenApi\Model\Response as OpenApiResponse;
use App\Controller\FileDownloadController;
use App\Entity\FileConversion as FileConversionEntity;
use App\Entity\FileUpload as FileUploadEntity;
use App\Enum\FileConversionStatus;
use App\Enum\FileOutputFormat;
use App\State\FileProvider;
use App\State\FileUploadProcessor;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    shortName: 'File',
    operations: [
        new Get(
            uriTemplate: '/files/{uid}',
            openapi: new OpenApiOperation(
                responses: [
                    '200' => new OpenApiResponse(
                        description: 'File status successfully retrieved',
                        content: new \ArrayObject([
                            'application/ld+json' => [
                                'example' => [
                                    '@context' => '/api/contexts/File',
                                    '@id' => '/api/files/f18f86b2-9bb8-48cf-9697-21c81802fa63',
                                    '@type' => 'File',
                                    'uid' => 'f18f86b2-9bb8-48cf-9697-21c81802fa63',
                                    'status' => 'finished',
                                    'links' => [
                                        'status' => '/api/files/f18f86b2-9bb8-48cf-9697-21c81802fa63',
                                        'download' => '/api/files/f18f86b2-9bb8-48cf-9697-21c81802fa63/download',
                                    ],
                                    'format' => 'json',
                                ],
                            ],
                        ])
                    ),
                ],
                summary: 'Get the status of a file conversion request',
                description: 'Returns the conversion status, requested output format, and HATEOAS links.'
            ),
            shortName: 'File',
            description: 'Get file conversion status.',
            name: 'file_status',
            provider: FileProvider::class,
            stateOptions: new Options(entityClass: FileConversionEntity::class)
        ),
        new Get(
            uriTemplate: '/files/{uid}/download',
            controller: FileDownloadController::class,
            openapi: new OpenApiOperation(
                responses: [
                    '200' => new OpenApiResponse(
                        description: 'Converted file',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'string',
                                    'format' => 'binary',
                                ],
                            ],
                            'application/xml' => [
                                'schema' => [
                                    'type' => 'string',
                                    'format' => 'binary',
                                ],
                            ],
                        ])
                    ),
                    '404' => new OpenApiResponse(
                        description: 'File not found or conversion not finished.'
                    ),
                ],
                summary: 'Download the converted file',
                description: 'Streams the converted file in the requested output format (json or xml).'
            ),
            output: false,
            read: false,
            name: 'file_download'
        ),
        new Post(
            uriTemplate: '/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            openapi: new OpenApiOperation(
                responses: [
                    '201' => new OpenApiResponse(
                        description: 'File conversion created',
                        content: new \ArrayObject([
                            'application/ld+json' => [
                                'example' => [
                                    '@context' => '/api/contexts/File',
                                    '@id' => '/api/files/f18f86b2-9bb8-48cf-9697-21c81802fa63',
                                    '@type' => 'File',
                                    'uid' => 'f18f86b2-9bb8-48cf-9697-21c81802fa63',
                                    'status' => 'queued',
                                    'links' => [
                                        'status' => '/api/files/f18f86b2-9bb8-48cf-9697-21c81802fa63',
                                    ],
                                    'format' => 'json',
                                ],
                            ],
                        ])
                    ),
                ],
                summary: 'Upload a file and request a conversion',
                description: 'Accepts a file (CSV/JSON/XLSX/ODS) and a desired output format (JSON or XML). Returns the created file conversion resource.',
                requestBody: new OpenApiRequestBody(
                    description: 'Multipart upload with the source file and desired formats.',
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary',
                                        'description' => 'The file to upload.',
                                    ],
                                    'requestedOutputFormat' => [
                                        'type' => 'string',
                                        'enum' => ['json', 'xml'],
                                        'description' => 'Desired output format.',
                                    ],
                                ],
                                'required' => ['file', 'requestedOutputFormat'],
                            ],
                            'example' => [
                                'file' => '(binary)',
                                'requestedOutputFormat' => 'json',
                            ],
                        ],
                    ])
                ),
            ),
            description: 'Upload a file for conversion.',
            denormalizationContext: ['groups' => ['file:write']],
            input: FileUploadEntity::class,
            name: 'file_upload',
            processor: FileUploadProcessor::class,
            stateOptions: new Options(entityClass: FileUploadEntity::class),
        ),
    ],
)]
#[Map(source: FileConversionEntity::class)]
final class File
{
    public Uuid $uid;
    #[ApiProperty(
        openapiContext: [
            'type' => 'string',
            'enum' => ['queued', 'processing', 'finished', 'failed'],
            'examples' => ['queued'],
            'description' => 'Current status of the file conversion.',
        ]
    )]
    public FileConversionStatus $status;
    #[Map(if: false)]
    public array $links = [];
    #[Map(if: false)]
    #[ApiProperty(
        openapiContext: [
            'type' => 'string',
            'enum' => ['json', 'xml'],
            'examples' => ['json'],
            'description' => 'Requested output format for the converted file.',
        ]
    )]
    public ?FileOutputFormat $format = null;
}
