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
use Illuminate\Support\Collection;
use function array_key_exists;
use function array_map;
use function array_search;
use function array_slice;
use function max;
use function min;

/**
 * Прысваенне 3ю разрада за 3 паспяховых старта у годзе
 */
final class StandardJuniorJuniorThirdRankChecker implements JuniorThirdRankChecker
{
    /** @var array<int, Collection<int, ProtocolLine>> */
    private array $preloadedLines = [];
    /** @var array<int, true> */
    private array $warmedIds = [];

    public function __construct(
        private readonly RankFactory $factory,
        private readonly Clock $clock,
        private readonly JuniorRankAgeValidator $validator,
        private readonly ProtocolLineRepository $protocols,
    ) {
    }

    public function warmUp(array $personIds, ?Carbon $date = null): void
    {
        if ($personIds === []) {
            return;
        }

        $years = $this->checkedYears($date);
        if ($years === []) {
            return;
        }

        $yearValues = array_map(static fn (Year $year): int => $year->value, $years);

        $lines = $this->protocols->byCriteria(new Criteria([
            'personIds' => $personIds,
            // dateFrom в критерии строгий (>), поэтому канун 1 января минимального года
            'dateFrom' => Carbon::create(min($yearValues) - 1, 12, 31)->endOfDay(),
            'dateTo' => Carbon::create(max($yearValues), 12, 31)->endOfDay(),
            'massCompetition' => false,
        ]));

        $this->preloadedLines = $lines->groupBy('person_id')->all();
        foreach ($personIds as $personId) {
            $this->warmedIds[(int) $personId] = true;
        }
    }

    public function check(int $personId, ?Carbon $date = null): ?Rank
    {
        foreach ($this->checkedYears($date) as $year) {
//            dump('$year: '. $year->toString());
//            dump('$isItJuniorRankAndCompletedAge: '. ($this->validator->validate($personId, Rank::JUNIOR_THIRD_RANK, $year) ? 'true' : 'false'));
            if (!$this->validator->validate($personId, Rank::JUNIOR_THIRD_RANK, $year)) {
                continue;
            }

            $lines = $this->yearLines($personId, $year);
//            dump('$lines: '. $lines->count());

            $results = $lines->filter(static fn (ProtocolLine $line): bool => $line->time !== null && !$line->vk);
//            dump('$results: '. $results->count());
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

    /** @return Year[] */
    private function checkedYears(?Carbon $date): array
    {
        $actualYear = $date ? Year::fromDate($date) : $this->clock->actualYear();
        $offset = array_search($actualYear, $this->clock->years());

        return array_slice($this->clock->years(), (int) $offset, 3);
    }

    private function yearLines(int $personId, Year $year): Collection
    {
        if (array_key_exists($personId, $this->warmedIds)) {
            return ($this->preloadedLines[$personId] ?? Collection::empty())
                ->filter(static fn (ProtocolLine $line): bool => (int) $line->event->date->year === $year->value)
                ->values()
            ;
        }

        return $this->protocols->byCriteria(new Criteria(['personId' => $personId, 'year' => $year, 'massCompetition' => false]));
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
