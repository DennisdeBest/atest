<?php

namespace App\Message;

use Symfony\Component\Uid\Uuid;

final readonly class FileConversion
{
    public function __construct(
        private Uuid $fileConversionUid,
    ) {
    }

    public function getFileConversionUid(): string
    {
        return $this->fileConversionUid;
    }
}
