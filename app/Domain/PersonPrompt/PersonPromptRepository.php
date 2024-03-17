<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

use App\Domain\PersonPayment\PersonPayment;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface PersonPromptRepository
{
    public function byId(int $id): ?PersonPrompt;

    public function byCriteria(Criteria $criteria): Collection;
}
