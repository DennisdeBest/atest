<?php

namespace App\Enum;

enum FileInputFormat: string
{
    case CSV = 'csv';

    case JSON = 'json';

    case XLSX = 'xlsx';

    case ODS = 'ods';

    public static function getValues(): array
    {
        return [self::CSV, self::JSON, self::XLSX, self::ODS];
    }
}
