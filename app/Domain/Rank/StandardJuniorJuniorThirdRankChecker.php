<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\Factory\RankFactory;
use App\Domain\Rank\Factory\RankInput;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;

/**
 * Прысваенне 3ю разрада за 3 паспяховых старта у гадзе
 */
final readonly class StandardJuniorJuniorThirdRankChecker implements JuniorThirdRankChecker
{
    public function __construct(
        private RankFactory $factory,
        private Clock $clock,
        private JuniorRankAgeValidator $validator,
        private ProtocolLineRepository $protocols,
    ) {
    }

    public function check(int $personId): ?Rank
    {
        $actualYear = $this->clock->actualYear();

        foreach($this->clock->years() as $year) {
            if (!$this->validator->validate($personId, Rank::JUNIOR_THIRD_RANK, $actualYear)) {
                continue;
            }

            $lines = $this->protocols->byCriteria(new Criteria(['person_id' => $personId, 'year' => $year]));
            $results = $lines->filter(static fn (ProtocolLine $line) => $line->time !== null && !$line->vk);
            if ($results->count() >= 3) {
                $results = $results
                    ->sortBy(static fn (ProtocolLine $line) => $line->event->date)
                    ->slice(offset: 0, length: 3)
                    ->values()
                ;

                return $this->factory->create($this->createRankInputFromProtocolLine($results->get(2)));
            }
        }

        return null;
    }

    private function createRankInputFromProtocolLine(ProtocolLine $line): RankInput
    {
        return new RankInput(
            personId: $line->person_id,
            eventId: $line->event->id,
            rank: Rank::JUNIOR_THIRD_RANK,
            startDate: $line->activate_rank ?: $line->event->date,
            activatedDate: $line->activate_rank,
        );
    }
}
