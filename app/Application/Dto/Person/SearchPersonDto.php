<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\AbstractDto;
use App\Domain\Rank\Rank;
use function array_key_exists;
use function array_map;
use function implode;

final class SearchPersonDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'clubId' => ['nullable', 'integer', 'min:1'],
            'rankId' => ['nullable', 'integer', 'in:' . implode(',', array_map(static fn (Rank $rank): int => $rank->value, Rank::cases()))],
        ];
    }

    public function __construct(public ?int $clubId = null, public ?int $rankId = null)
    {
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        if (array_key_exists('clubId', $data)) {
            $this->clubId = (int) $data['clubId'];
        }
        if (array_key_exists('rankId', $data)) {
            $this->rankId = (int) $data['rankId'];
        }

        return $this;
    }
}
