<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\FileUpload;
use App\Entity\FileConversion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class FileUploadProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
        #[Autowire(env: 'UPLOAD_DIR')]
        private string                 $uploadDir
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): FileUpload
    {
        /** @var FileUpload $data */
        $file = $data->file;


        if ($file instanceof UploadedFile) {
            $name = $data->getUid()?->toRfc4122() . '.' . $file->guessExtension();

            // Move the uploaded file to the queue
            $file->move($this->uploadDir, $name);

            $data->setPath('');
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        $fileConversion = new FileConversion()->setUpload($data);
        $this->entityManager->persist($fileConversion);
        $this->entityManager->flush();

        $conversionMessage = new \App\Message\FileConversion($fileConversion);

        $this->messageBus->dispatch($conversionMessage);

        return $data;
    }
}
