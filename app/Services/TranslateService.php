<?php

namespace App\Services;

use Illuminate\Cache\Repository as CacheManager;

class TranslateService
{
    public function __construct(private readonly CacheManager $cache)
    {}

    public function translate(string $value, string $locale = UserService::BY_LOCALE): string
    {
        return $this->cache->rememberForever(
            crc32($value),
            fn () => $value
        );
    }
}
