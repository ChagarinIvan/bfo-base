<?php

declare(strict_types=1);

namespace App\Infrastracture\Integration\OrientBy;
use function array_map;
use function file_get_contents;
use function json_decode;

class OrientByApiClient
{
    private const ORIENT_BY_PERSONS_API_URL = 'http://orient.by/api/?type=getSportmens';

    /**
     * @return OrientByPersonDto[]
     */
    public function getPersons(): array
    {
        $apiResponse = file_get_contents(self::ORIENT_BY_PERSONS_API_URL);
        $personsData = json_decode($apiResponse, true);

        return array_map($this->extractPersonDto(...), $personsData);
    }

    private function extractPersonDto(array $data): OrientByPersonDto
    {
        return new OrientByPersonDto(
            name: $data['name'],
            yob: isset($data['yob']) ? (int)$data['yob'] : null,
            club: $data['club'] ?? null,
            rank: $data['rank'] ?? null,
            paid: (bool)$data['paid'],
            paymentDate: $data['bfopaydate'] ?? null,
        );
    }
}
