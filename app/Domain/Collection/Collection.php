<?php

namespace App\Domain\Collection;

use App\Domain\Hasher\Hasher;
use Stringable;

interface Collection
{
    public function hash();
}
