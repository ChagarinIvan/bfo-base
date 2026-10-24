<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\Auth\UserId;
use App\Domain\Shared\Criteria;

final readonly class CorrectPersonRanks
{
    public function __construct(public UserId $userId, public ?int $personId = null, public bool $apply = false)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria($this->personId === null ? [] : ['ids' => [$this->personId]]);
    }
}
