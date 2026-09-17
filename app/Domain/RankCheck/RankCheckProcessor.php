<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

use App\Domain\RankCheck\Exception\ProcessError;

interface RankCheckProcessor
{
    /** @throws ProcessError */
    public function process(RankCheck $check): void;
}
