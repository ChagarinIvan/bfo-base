<?php

declare(strict_types=1);

namespace App\Application\Dto\Distance;

use App\Application\Dto\AbstractDto;

final class DistanceSearchDto extends AbstractDto
{
    public string $eventId;

    public static function requestValidationRules(): array
    {
        return ['eventId' => ['required', 'integer', 'min:1']];
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        $this->eventId = (string) $data['eventId'];

        return $this;
    }
}
