<?php

declare(strict_types=1);

namespace App\Application\Dto\ProtocolLine;

use App\Application\Dto\AbstractDto;
use function array_key_exists;
use function is_string;
use function trim;

final class SearchProtocolLineDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'personId' => ['required_without:distanceId', 'nullable', 'integer', 'min:1'],
            'distanceId' => ['required_without:personId', 'nullable', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'min:1', 'max:255'],
            'withClub' => ['nullable', 'boolean'],
            'withEvent' => ['nullable', 'boolean'],
            'withCompetition' => ['nullable', 'boolean'],
            'year' => ['nullable', 'numeric', 'digits:4'],
            'competitionName' => ['nullable', 'string', 'min:3', 'max:255'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public static function normaliseRequestData(array $data): array
    {
        if (array_key_exists('competitionName', $data) && is_string($data['competitionName'])) {
            $data['competitionName'] = trim($data['competitionName']);

            if ($data['competitionName'] === '') {
                unset($data['competitionName']);
            }
        }

        return $data;
    }

    public function __construct(
        public ?string $personId = null,
        public ?string $distanceId = null,
        public ?string $name = null,
        public ?string $withClub = null,
        public ?string $withEvent = null,
        public ?string $withCompetition = null,
        public ?string $year = null,
        public ?string $competitionName = null,
        public ?string $date = null,
    ) {
    }

    /** @param array<string, mixed> $data */
    public function fromArray(array $data): self
    {
        $this->setStringParam('personId', $data);
        $this->setStringParam('distanceId', $data);
        $this->setStringParam('name', $data);
        $this->setStringParam('withClub', $data);
        $this->setStringParam('withEvent', $data);
        $this->setStringParam('withCompetition', $data);
        $this->setStringParam('year', $data);
        $this->setStringParam('competitionName', $data);
        $this->setStringParam('date', $data);

        return $this;
    }
}
