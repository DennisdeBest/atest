<?php

namespace App\Enum;

enum FileOutputFormat: string
{
    case JSON = 'json';
    case XML = 'xml';

    public static function getValues(): array
    {
        return [self::JSON->value, self::XML->value];
    }
}
