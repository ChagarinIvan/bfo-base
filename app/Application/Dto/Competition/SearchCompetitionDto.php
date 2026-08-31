<?php

declare(strict_types=1);

namespace App\Application\Dto\Competition;

use App\Application\Dto\AbstractDto;
use App\Models\Year;
use Illuminate\Validation\Rules\Enum;
use function array_key_exists;
use function is_string;
use function trim;

final class SearchCompetitionDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'year' => ['nullable', new Enum(Year::class)],
            'name' => ['nullable', 'string', 'min:3', 'max:255'],
            'date' => ['nullable', 'date_format:Y-m-d'],
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
        public ?string $year = null,
        public ?string $name = null,
        public ?string $date = null,
    ) {
    }

    public function fromArray(array $data): self
    {
        $this->setStringParam('year', $data);
        $this->setStringParam('name', $data);
        $this->setStringParam('date', $data);

        return $this;
    }
}
