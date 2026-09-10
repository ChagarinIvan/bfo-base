<?php

declare(strict_types=1);

namespace App\Application\Service\ProtocolLine;

use App\Application\Dto\ProtocolLine\SearchProtocolLineDto;
use App\Domain\ProtocolLine\ProtocolLineResources;
use App\Domain\Shared\Criteria;
use function array_filter;

final readonly class ListProtocolLines
{
    public function __construct(private SearchProtocolLineDto $search)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria(array_filter([
            'personId' => $this->search->personId,
            'distanceId' => $this->search->distanceId,
            'name' => $this->search->name,
            'year' => $this->search->year,
            'competitionName' => $this->search->competitionName,
            'date' => $this->search->date,
        ], static fn (mixed $value): bool => $value !== null));
    }

    public function resources(): ProtocolLineResources
    {
        return new ProtocolLineResources(
            withEvent: $this->search->withEvent === '1',
            withCompetition: $this->search->withCompetition === '1',
            withClub: $this->search->withClub === '1',
        );
    }
}
