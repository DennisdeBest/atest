<?php

namespace App\Encoder;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

final class MultipartDecoder implements DecoderInterface
{
    public const string FORMAT = 'multipart';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function decode(string $data, string $format, array $context = []): ?array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return null;
        }

        $fields = $request->request->all();

        $decoded = array_map(
            static function ($element) {
                if (!is_string($element)) {
                    return $element;
                }

                $trimmed = ltrim($element);

                if ('' === $trimmed || !in_array($trimmed[0], ['{', '[', '"'], true)) {
                    return $element;
                }

                try {
                    return json_decode($element, true, flags: \JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    return $element;
                }
            },
            $fields
        );

        return $decoded + $request->files->all();
    }

    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}
