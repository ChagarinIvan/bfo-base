<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;

final class UniteEventsDto extends AbstractDto
{
    /** @var list<int> */
    public array $eventIds;

    public static function requestValidationRules(): array
    {
        return [
            'eventIds' => 'required|array|min:2',
            'eventIds.*' => 'required|integer|distinct',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->eventIds = $data['eventIds'];

        return $this;
    }
}
