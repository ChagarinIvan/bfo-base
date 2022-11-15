<?php

namespace App\Services;

use Illuminate\Cache\Repository as CacheManager;
use Google\Cloud\Translate\V2\TranslateClient;

class TranslateService
{
    private readonly TranslateClient $translator;

    public function __construct(private readonly CacheManager $cache)
    {
        $this->translator = new TranslateClient(['key' => env('GOOGLE_AUTH_KEY')]);
    }

    public function translate(string $value, string $locale = UserService::BY_LOCALE): string
    {
        return $this->cache->rememberForever(
            crc32($value),
            fn () => $this->translator->translate($value, ['target' => $locale])['text'] ?? $value
        );
    }
}
