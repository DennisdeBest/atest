<?php

namespace App\Enum;

enum FileConversionStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Finished = 'finished';
    case Failed = 'failed';
}
