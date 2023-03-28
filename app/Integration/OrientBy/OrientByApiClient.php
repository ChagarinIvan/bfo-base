<?php

declare(strict_types=1);

namespace App\Integration\OrientBy;

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
            $data['name'],
            isset($data['yob']) ? (int)$data['yob'] : null,
            $data['club'] ?? null,
            $data['rank'] ?? null,
            (bool)$data['paid'],
        );
    }
}
