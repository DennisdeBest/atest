<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\FileUpload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileUploadProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private string                 $uploadDir = __DIR__ . '/../../public/uploads'
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): FileUpload
    {
        /** @var FileUpload $data */
        $file = $data->file;

        if ($file instanceof UploadedFile) {
            $name = $data->getUid()?->toRfc4122() . '.' . $file->guessExtension();
            $file->move($this->uploadDir, $name);

            $data->setPath('/uploads/' . $name);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
