<?php

namespace App\MessageHandler;

use App\Enum\FileConversionStatus;
use App\Message\FileConversion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FileConversionHandler
{


    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    public function __invoke(
        FileConversion $message
    ): void
    {
        $conversion = $message->getFileConversion();
        $conversion->setStatus(FileConversionStatus::Processing);

        $this->entityManager->persist($conversion);
        $this->entityManager->flush();


        sleep(30);

        //TODO move the file to a completed location

        $conversion->setStatus(FileConversionStatus::Finished);

        $this->entityManager->persist($conversion);
        $this->entityManager->flush();
    }

}
