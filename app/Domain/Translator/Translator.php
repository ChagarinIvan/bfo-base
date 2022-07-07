<?php

namespace App\Domain\Translator;

use Stringable;

interface Translator
{
    public function translate(mixed $value): mixed;
}
