<?php

namespace App\MessageHandler;

use App\Enum\FileConversionStatus;
use App\Message\FileConversion;
use App\Entity\FileConversion as FileConversionEntity;
use App\Repository\FileConversionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class FileConversionHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileConversionRepository $fileConversionRepository,
        #[Autowire(env: 'CONVERSION_DELAY')]
        private int $conversionDelay,
        #[Autowire(env: 'UPLOAD_DIR')]
        private string $uploadDir,
        #[Autowire(env: 'CONVERSION_DIR')]
        private string $conversionDir,
    ) {
    }

    public function __invoke(
        FileConversion $message,
    ): void {
        $conversion = $this->fileConversionRepository->findOneBy([
            'uid' => Uuid::fromString($message->getFileConversionUid()),
        ]);

        if (!$conversion instanceof FileConversionEntity) {
            return;
        }

        $conversion->setStatus(FileConversionStatus::Processing);

        $this->entityManager->persist($conversion);
        $this->entityManager->flush();

        sleep($this->conversionDelay);

        $filesystem = new Filesystem();

        $upload = $conversion->getUpload();
        $uid = $upload->getUid();

        $filesystem->copy(
            sprintf('%s/%s.%s', $this->uploadDir, $uid, $upload->getInputFormat()->value),
            sprintf('%s/%s.%s', $this->conversionDir, $uid, $upload->getRequestedOutputFormat()->value),
        );

        $conversion->setStatus(FileConversionStatus::Finished);

        $this->entityManager->persist($conversion);
        $this->entityManager->flush();
    }
}
