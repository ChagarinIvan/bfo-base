<?php

namespace App\Domain\Cache;

interface Cache
{
    public function cache(string $key, callable $callable): mixed;
}
