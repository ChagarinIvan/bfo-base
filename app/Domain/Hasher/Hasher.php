<?php

namespace App\Domain\Hasher;

interface Hasher
{
    public function hash(mixed $value): string;
}
