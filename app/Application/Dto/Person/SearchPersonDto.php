<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\AbstractDto;
use App\Domain\Rank\Rank;
use function array_key_exists;
use function array_map;
use function date;
use function implode;
use function is_string;
use function trim;

final class SearchPersonDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'name' => ['nullable', 'string', 'min:3', 'max:255'],
            'clubId' => ['nullable', 'integer', 'min:1'],
            'rankId' => ['nullable', 'integer', 'in:' . implode(',', array_map(static fn (Rank $rank): int => $rank->value, Rank::cases()))],
            'birthYear' => ['nullable', 'integer', 'min:1920', 'max:' . date('Y')],
        ];
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
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
        public ?string $name = null,
        public ?int $clubId = null,
        public ?int $rankId = null,
        public ?int $birthYear = null,
        public ?bool $withoutLinesAndPayments = null,
        public ?array $ids = null,
    )
    {
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        if (array_key_exists('name', $data) && is_string($data['name'])) {
            $this->name = $data['name'];
        }
        if (array_key_exists('clubId', $data)) {
            $this->clubId = (int) $data['clubId'];
        }
        if (array_key_exists('rankId', $data)) {
            $this->rankId = (int) $data['rankId'];
        }
        if (array_key_exists('birthYear', $data)) {
            $this->birthYear = (int) $data['birthYear'];
        }

        return $this;
    }
}
