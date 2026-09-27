<?php

declare(strict_types=1);

namespace App\Application\Dto\CupEvent;

use App\Application\Dto\AbstractDto;

final class CreateCupEventDto extends AbstractDto
{
    public int $cupId;
    public int $eventId;
    public float $points;

    public static function requestValidationRules(): array
    {
        return [
            'cupId' => 'required|integer|min:1',
            'eventId' => 'required|integer|min:1',
            'points' => 'required|numeric|min:0',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->cupId = (int) $data['cupId'];
        $this->eventId = (int) $data['eventId'];
        $this->points = (float) $data['points'];

        return $this;
    }
}
