<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\FileUpload;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class FileUploadProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        #[Autowire(env: 'UPLOAD_DIR')]
        private string $uploadDir
    )
    {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): FileUpload
    {
        $errors = $this->validator->validate($data);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

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
