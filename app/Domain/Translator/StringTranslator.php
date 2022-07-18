<?php

namespace App\Domain\Translator;

use App\Domain\Cache\Cache;
use App\Domain\Hasher\Hasher;

final class StringTranslator implements Translator
{
    public function __construct(
        private readonly Translator $decorated,
    ) {}

    public function translate(mixed $value): mixed
    {
        return $this->cache->cache(
            $this->hasher->hash($value),
            fn(mixed $value): mixed => $this->decorated->translate($value)
        );
    }
}
