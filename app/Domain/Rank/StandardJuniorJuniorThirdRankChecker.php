<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\Factory\RankFactory;
use App\Domain\Rank\Factory\RankInput;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;
use App\Models\Year;
use Carbon\Carbon;
use function array_slice;

/**
 * Прысваенне 3ю разрада за 3 паспяховых старта у годзе
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

    public function check(int $personId, ?Carbon $date = null): ?Rank
    {
        $actualYear = $date ? Year::fromDate($date) : $this->clock->actualYear();
        dump('$actualYear: '. $actualYear->toString());

        $offset = array_search($actualYear, $this->clock->years());
        dump($offset);
        dump(array_slice($this->clock->years(), $offset, 3));

        foreach(array_slice($this->clock->years(), $offset, 3) as $year) {
            dump('$year: '. $actualYear->toString());
            dump('$isItJuniorRankAndCompletedAge: '. ($this->validator->validate($personId, Rank::JUNIOR_THIRD_RANK, $year) ? 'true' : 'false'));
            if (!$this->validator->validate($personId, Rank::JUNIOR_THIRD_RANK, $year)) {
                continue;
            }

            $lines = $this->protocols->byCriteria(new Criteria(['personId' => $personId, 'year' => $year]));
            dump('$lines: '. $lines->count());

            $results = $lines->filter(static fn (ProtocolLine $line) => $line->time !== null && !$line->vk);
            dump('$results: '. $results->count());
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
