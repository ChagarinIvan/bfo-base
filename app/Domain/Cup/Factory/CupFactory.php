<?php

declare(strict_types=1);

namespace App\Domain\Cup\Factory;

use App\Domain\Cup\Cup;

interface CupFactory
{
    public function create(CupInput $input): Cup;
}
