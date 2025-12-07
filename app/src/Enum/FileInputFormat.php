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
        return array_column(self::cases(), 'value');
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::CSV  => 'text/csv',
            self::JSON => 'application/json',
            self::XLSX => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            self::ODS  => 'application/vnd.oasis.opendocument.spreadsheet',
        };
    }

    public function extension(): string
    {
        return $this->value;
    }
}
