<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\File;
use App\Entity\FileConversion;
use App\Entity\FileUpload;
use App\Enum\FileInputFormat;
use App\Service\FileConversionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class FileUploadProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
        private FileConversionService $fileConversionService,
        #[Autowire(env: 'UPLOAD_DIR')]
        private string $uploadDir,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): File
    {
        /** @var FileUpload $data */
        $file = $data->file;

        if ($file instanceof UploadedFile) {
            $name = $data->getUid()?->toRfc4122().'.'.$file->guessExtension();
            $inputFormat = FileInputFormat::from($file->guessExtension());
            $data->setInputFormat($inputFormat);

            $data->setFilename($this->getFilename($file));

            // Move the uploaded file to the queue
            $file->move($this->uploadDir, $name);
        }

        // Save the FileUpload entity
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        // Initiate the conversion
        $fileConversion = new FileConversion()->setUpload($data);

        $this->entityManager->persist($fileConversion);
        $this->entityManager->flush();

        $conversionMessage = new \App\Message\FileConversion($fileConversion);

        $this->messageBus->dispatch($conversionMessage);

        return $this->fileConversionService->map($fileConversion);
    }

    private function getFilename(UploadedFile $file): string
    {
        return pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    }
}
