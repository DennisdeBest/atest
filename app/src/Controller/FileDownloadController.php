<?php

namespace App\Controller;

use App\Enum\FileConversionStatus;
use App\Enum\FileOutputFormat;
use App\Repository\FileConversionRepository;
use App\Service\FileConversionService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

#[AsController]
final readonly class FileDownloadController
{
    public function __construct(
        private FileConversionRepository $fileConversionRepository,
        private FileConversionService $fileConversionService,
    ) {
    }

    public function __invoke(string $uid): BinaryFileResponse
    {
        $entity = $this->fileConversionRepository->findOneBy(['uid' => Uuid::fromString($uid)]);

        if (!$entity) {
            throw new NotFoundHttpException('File not found.');
        }

        if ($entity->getStatus() !== FileConversionStatus::Finished) {
            throw new NotFoundHttpException('File not ready.');
        }

        $path = $this->fileConversionService->getConvertedFilePath($entity);

        if (!is_file($path)) {
            throw new NotFoundHttpException('Converted file not found.');
        }

        $upload = $entity->getUpload();
        /** @var FileOutputFormat $format */
        $format = $upload->getRequestedOutputFormat();

        $filename = sprintf(
            '%s.%s',
            $upload->getFilename(),
            $format->extension(),
        );

        $response = new BinaryFileResponse($path);

        $response->headers->set('Content-Type', $format->mimeType());

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
        );

        return $response;
    }
}
