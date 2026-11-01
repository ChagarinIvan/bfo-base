<?php

declare(strict_types=1);

namespace Tests\Feature\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Handler\Rank\ProtocolLineRankActivatedHandler;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Bridge\Laravel\Jobs\RebuildPersonRankJob;
use App\Domain\Auth\Impression;
use App\Domain\Person\Person;
use App\Domain\Person\PersonInfo;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculationReason;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFactsCollector;
use App\Domain\ProtocolLine\Event\ProtocolLineRankActivated;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\Clock;
use App\Domain\Shared\FrozenClock;
use App\Domain\Shared\TransactionManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Testing\Fakes\EventFake;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;
use function serialize;
use function unserialize;

final class PersonRankCalculationTest extends TestCase
{
    use RefreshDatabase;

    /** @return iterable<string, array{RankCalculationReason}> */
    public static function reasons(): iterable
    {
        foreach (RankCalculationReason::cases() as $reason) {
            yield $reason->value => [$reason];
        }
    }

    #[Test]
    public function a_successful_rebuild_records_execution_time_even_when_the_rank_is_unchanged(): void
    {
        $person = Person::factory()->createOne();
        $this->assertNull($person->fresh()->rank_calculated_at);
        $this->assertNull($person->fresh()->rank_calculation_reason);
        $this->at('2026-11-01 12:00:00');
        $this->rebuild($person);
        $person->refresh();
        $rank = $person->current_rank;
        $this->assertSame('2026-11-01 12:00:00', $person->rank_calculated_at?->toDateTimeString());
        $this->assertSame('manual', $person->rank_calculation_reason?->value);

        $this->at('2026-11-01 13:00:00');
        $this->rebuild($person);
        $person->refresh();
        $this->assertSame($rank, $person->current_rank);
        $this->assertSame('2026-11-01 13:00:00', $person->rank_calculated_at?->toDateTimeString());
        $this->assertSame(42, $person->updated->by);

        $person->updateInfo(new PersonInfo('Changed', $person->lastname, $person->birthday, $person->citizenship, $person->club_id), new Impression(Carbon::parse('2026-11-02'), 43));
        $this->app->make(PersonRepository::class)->update($person);
        $this->assertSame('2026-11-01 13:00:00', $person->fresh()->rank_calculated_at?->toDateTimeString());
        $this->assertSame('manual', $person->fresh()->rank_calculation_reason?->value);
    }

    #[Test]
    public function a_failure_after_saving_the_projection_rolls_back_the_metadata(): void
    {
        $this->app->instance('events', new EventFake($this->app->make('events')));
        $person = Person::factory()->createOne();
        $this->at('2026-11-01 12:00:00');
        $this->rebuild($person);
        $previous = $person->fresh()->getAttributes();
        $repository = $this->app->make(PersonRepository::class);
        $persons = $this->createMock(PersonRepository::class);
        $persons->method('lockById')->willReturn($person->fresh());
        $persons->expects($this->once())->method('update')->willReturnCallback(static function (Person $updated) use ($repository): never {
            $repository->update($updated);
            throw new RuntimeException('persistence failed');
        });
        $service = new RebuildPersonRanksService($persons, $this->app->make(RankFactsCollector::class), new RankCalculator(), new FrozenClock(Carbon::parse('2026-11-02')), $this->app->make(TransactionManager::class));

        try {
            $service->execute(new RebuildPersonRanks($person->id, new UserId(42), RankCalculationReason::Protocol));
            $this->fail('Expected persistence failure');
        } catch (RuntimeException $error) {
            $this->assertSame('persistence failed', $error->getMessage());
        }
        $this->assertSame($previous, $person->fresh()->getAttributes());
    }

    #[Test]
    public function a_failed_calculation_keeps_the_previous_time_and_reason(): void
    {
        $person = Person::factory()->createOne();
        $this->at('2026-11-01 12:00:00');
        $this->rebuild($person);
        $previous = $person->fresh()->getAttributes();
        $facts = $this->createMock(RankFactsCollector::class);
        $facts->expects($this->once())->method('collect')->with($person->id)->willThrowException(new RuntimeException('calculation failed'));
        $this->app->instance(RankFactsCollector::class, $facts);
        $this->at('2026-11-02');
        try {
            $this->rebuild($person);
            $this->fail('Expected calculation failure');
        } catch (RuntimeException $error) {
            $this->assertSame('calculation failed', $error->getMessage());
        }
        $this->assertSame($previous, $person->fresh()->getAttributes());
    }

    #[Test]
    #[DataProvider('reasons')]
    public function a_queued_calculation_preserves_reason_and_uses_execution_time(RankCalculationReason $reason): void
    {
        $person = Person::factory()->createOne();
        $job = new RebuildPersonRankJob($person->id, new Impression(Carbon::parse('2020-01-01'), 42), $reason);
        $restored = unserialize(serialize($job));
        $this->assertInstanceOf(RebuildPersonRankJob::class, $restored);
        $this->at('2026-11-01 14:00:00');
        $restored->handle($this->app->make(RebuildPersonRanksService::class));

        $person->refresh();
        $this->assertSame($reason, $person->rank_calculation_reason);
        $this->assertSame('2026-11-01 14:00:00', $person->rank_calculated_at?->toDateTimeString());
        $this->assertSame(42, $person->updated->by);
    }

    #[Test]
    public function an_old_serialized_job_without_reason_keeps_the_manual_default(): void
    {
        $person = Person::factory()->createOne();
        $job = new RebuildPersonRankJob($person->id, new Impression(Carbon::parse('2020-01-01'), 42));
        unset($job->reason);
        $restored = unserialize(serialize($job));
        $this->assertInstanceOf(RebuildPersonRankJob::class, $restored);
        $restored->handle($this->app->make(RebuildPersonRanksService::class));
        $this->assertSame(RankCalculationReason::Manual, $person->fresh()->rank_calculation_reason);
    }

    #[Test]
    public function changing_a_rank_activation_date_recalculates_with_the_manual_reason(): void
    {
        $person = Person::factory()->createOne();
        $line = ProtocolLine::factory()->makeOne(['person_id' => $person->id]);
        $this->at('2026-11-01 15:00:00');
        $this->app->make(ProtocolLineRankActivatedHandler::class)->handle(new ProtocolLineRankActivated($line, new Impression(Carbon::parse('2020-01-01'), 42)));
        $this->assertSame(RankCalculationReason::Manual, $person->fresh()->rank_calculation_reason);
        $this->assertSame('2026-11-01 15:00:00', $person->fresh()->rank_calculated_at?->toDateTimeString());
    }

    private function at(string $time): void
    {
        $this->app->instance(Clock::class, new FrozenClock(Carbon::parse($time)));
    }

    private function rebuild(Person $person): void
    {
        $this->app->make(RebuildPersonRanksService::class)->execute(new RebuildPersonRanks($person->id, new UserId(42)));
    }
}
