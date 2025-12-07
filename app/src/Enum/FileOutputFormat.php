<?php

namespace App\Enum;

enum FileOutputFormat: string
{
    case JSON = 'json';
    case XML = 'xml';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function mimeType(): string
    {
        return match ($this) {
            self::JSON => 'application/json',
            self::XML  => 'application/xml',
        };
    }

    public function extension(): string
    {
        return $this->value;
    }
}
