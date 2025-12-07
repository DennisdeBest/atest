<?php

namespace App\Service;

use App\ApiResource\File;
use App\Entity\FileConversion;
use App\Enum\FileConversionStatus;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

final readonly class FileConversionService
{
    public function __construct(
        private ObjectMapperInterface $objectMapper,
        #[Autowire(env: 'CONVERSION_DIR')]
        private string $conversionDir,
    ) {
    }

    public function map(FileConversion $input): File
    {
        $file = new File();
        $this->objectMapper->map($input, $file);

        $file->links = $this->getLinks($input);
        $file->format = $input->getUpload()->getOutputFormat();

        return $file;
    }

    public function getConvertedFilePath(FileConversion $entity): string
    {
        $upload = $entity->getUpload();
        $extension = $upload->getOutputFormat()->value;

        return sprintf('%s/%s.%s',
            $this->conversionDir,
            $upload->getUid(),
            $extension
        );
    }

    private function getLinks(FileConversion $entity): array
    {
        $status = $entity->getStatus();

        $links = [
            'status' => sprintf('/api/files/%s', $entity->getUid()),
        ];

        return match ($status) {
            FileConversionStatus::Finished => [
                ...$links,
                'download' => sprintf('/api/files/%s/download', $entity->getUid()),
            ],
            default => $links,
        };
    }
}
