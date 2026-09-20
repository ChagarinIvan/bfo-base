<?php

declare(strict_types=1);

namespace App\Application\Dto\CupEvent;

use App\Application\Dto\AbstractDto;
use function array_key_exists;
use function array_map;
use function is_string;
use function trim;

final class CupEventSearchDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'cupId' => 'nullable|integer|min:1',
            'eventIds' => 'nullable|array',
            'eventIds.*' => 'integer|min:1',
            'name' => 'nullable|string|min:3|max:255',
            'date' => 'nullable|date_format:Y-m-d',
        ];
    }

    /** @param array<string, mixed> $data */
    public static function normaliseRequestData(array $data): array
    {
        if (array_key_exists('name', $data) && is_string($data['name'])) {
            $data['name'] = trim($data['name']);
            if ($data['name'] === '') {
                unset($data['name']);
            }
        }

        return $data;
    }

    public function __construct(
        public ?string $cupId = null,
        /** @var list<string>|null */
        public ?array $eventIds = null,
        public ?string $name = null,
        public ?string $date = null,
    )
    {
    }

    public function fromArray(array $data): self
    {
        $this->setStringParam('cupId', $data);
        if (array_key_exists('eventIds', $data)) {
            /** @var list<int|string> $eventIds */
            $eventIds = $data['eventIds'];
            $this->eventIds = array_map(static fn (int|string $eventId): string => (string) $eventId, $eventIds);
        }
        $this->setStringParam('name', $data);
        $this->setStringParam('date', $data);

        return $this;
    }
}
