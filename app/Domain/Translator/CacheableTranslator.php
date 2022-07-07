<?php

namespace App\Domain\Translator;

use App\Domain\Cache\Cache;

final class CacheableTranslator implements Translator
{
    public function __construct(
        private readonly Cache $cache,
        private readonly Translator $decorated,
    ) {}

    public function translate(mixed $value): mixed
    {
        return $this->cache->cache($value, fn() => $this->decorated->translate($value));
    }
}
