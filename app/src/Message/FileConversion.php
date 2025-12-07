<?php

namespace App\Message;

use App\Entity\FileConversion as FileConversionEntity;

class FileConversion
{
    public function __construct(private FileConversionEntity $fileConversion)
    {
    }

    public function getFileConversion(): FileConversionEntity
    {
        return $this->fileConversion;
    }
}
