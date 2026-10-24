<?php

declare(strict_types=1);

namespace App\Domain\Person\Audit;

use App\Domain\Auth\Impression;
use InvalidArgumentException;

final readonly class RankAuditEntry
{
    public function __construct(
        public int $personId,
        public RankAuditTrigger $trigger,
        public Impression $created,
        public ?RankSnapshot $before,
        public ?RankSnapshot $after,
        public ?int $id = null,
    ) {
        if ($after !== null && $before === null) {
            throw new InvalidArgumentException('A completed rank change requires its previous state.');
        }
    }
}
