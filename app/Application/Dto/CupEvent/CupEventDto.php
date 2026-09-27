<?php

declare(strict_types=1);

namespace App\Application\Dto\CupEvent;

use App\Application\Dto\AbstractDto;

final class CupEventDto extends AbstractDto
{
    public int $eventId;
    public float $points;

    public static function requestValidationRules(): array
    {
        return ['eventId' => 'required|integer|min:1', 'points' => 'required|numeric|min:0'];
    }

    public function fromArray(array $data): self
    {
        $this->eventId = (int) $data['eventId'];
        $this->points = (float) $data['points'];

        return $this;
    }
}
