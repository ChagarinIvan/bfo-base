<?php

declare(strict_types=1);

namespace App\Application\Dto\Person\RankAudit;

use App\Application\Dto\Auth\ImpressionDto;
use App\Domain\Person\Audit\RankAuditTrigger;

final readonly class RankAuditDto
{
    /** @param array<string, mixed>|null $before
     * @param array<string, mixed>|null $after
     */
    public function __construct(
        public string $id,
        public int $personId,
        public RankAuditTrigger $trigger,
        public ImpressionDto $created,
        public ?array $before,
        public ?array $after,
    ) {
    }
}
