<?php

declare(strict_types=1);

namespace App\Application\Service\ProtocolLine;

use App\Application\Dto\Auth\UserId;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\Criteria;
use function array_unique;
use function array_values;

final readonly class SetPersonToProtocolLines
{
    public function __construct(
        private string $preparedProtocolLine,
        private int $personId,
        private UserId $userId,
        public ?int $previousPersonId = null,
    ) {
    }

    /**
     * @param iterable<ProtocolLine> $lines
     * @return list<int>
     */
    public function previousPersonIds(iterable $lines): array
    {
        $ids = [];
        if ($this->previousPersonId !== null && $this->previousPersonId !== $this->personId) {
            $ids[] = $this->previousPersonId;
        }
        foreach ($lines as $line) {
            if ($line->person_id !== null && $line->person_id !== $this->personId) {
                $ids[] = $line->person_id;
            }
        }

        return array_values(array_unique($ids));
    }

    public function criteria(): Criteria
    {
        return new Criteria(['preparedLine' => $this->preparedProtocolLine]);
    }

    public function preparedProtocolLine(): string
    {
        return $this->preparedProtocolLine;
    }

    public function personId(): int
    {
        return $this->personId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }
}
