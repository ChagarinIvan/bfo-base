<?php

declare(strict_types=1);

namespace Tests\Feature\Person;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Event\Event;
use App\Domain\Person\Exception\RanksUpdatingError;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFact;
use App\Domain\Person\RankFactsCollector;
use App\Domain\Person\StandardEventPersonRankUpdater;
use App\Domain\ProtocolLine\ProtocolLineOperations;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StandardEventPersonRankUpdaterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_rebuilds_each_affected_person_using_the_stage_impression(): void
    {
        $first = Person::factory()->createOne(['id' => 501, 'active' => true]);
        $second = Person::factory()->createOne(['id' => 502, 'active' => true]);
        $event = Event::factory()->createOne(['competition_id' => Competition::factory()->createOne()->id]);
        $impression = new Impression(Carbon::parse('2026-10-03 12:00:00'), 42);
        $operations = $this->createMock(ProtocolLineOperations::class);
        $operations->expects($this->once())->method('personIdsForEvent')->with($event)
            ->willReturn([$first->id, $second->id]);
        $facts = $this->createMock(RankFactsCollector::class);
        $facts->expects($this->exactly(2))->method('collect')->willReturnCallback(
            static function (int $personId) use ($first, $second): array {
                self::assertContains($personId, [$first->id, $second->id]);

                return [];
            },
        );

        $this->updater($operations, $facts)->update($event, $impression);

        foreach ([$first, $second] as $person) {
            $person->refresh();
            $this->assertSame(Rank::WithoutRank, $person->current_rank);
            $this->assertSame(42, $person->updated->by);
            $this->assertTrue($person->updated->at->equalTo($impression->at));
        }
    }

    #[Test]
    public function it_reports_a_missing_affected_person_as_a_rank_stage_error(): void
    {
        $event = Event::factory()->createOne(['competition_id' => Competition::factory()->createOne()->id]);
        $operations = $this->createStub(ProtocolLineOperations::class);
        $operations->method('personIdsForEvent')->willReturn([999999]);
        $facts = $this->createMock(RankFactsCollector::class);
        $facts->expects($this->never())->method('collect');

        $this->expectException(RanksUpdatingError::class);
        $this->updater($operations, $facts)->update($event, new Impression(Carbon::now(), 42));
    }

    #[Test]
    public function it_calculates_rank_from_collected_facts_before_saving(): void
    {
        $person = Person::factory()->createOne(['id' => 503, 'active' => true]);
        $event = Event::factory()->createOne(['competition_id' => Competition::factory()->createOne()->id]);
        $impression = new Impression(Carbon::parse('2026-10-03 12:00:00'), 42);
        $operations = $this->createStub(ProtocolLineOperations::class);
        $operations->method('personIdsForEvent')->willReturn([$person->id]);
        $facts = $this->createMock(RankFactsCollector::class);
        $facts->expects($this->once())->method('collect')->with($person->id)->willReturn([
            new RankFact(10, 20, $event->id, $event->competition_id, Rank::FirstRank, Carbon::parse('2026-01-01'), null),
        ]);
        $persons = $this->createMock(PersonRepository::class);
        $persons->expects($this->once())->method('lockById')->with($person->id)->willReturn($person);
        $persons->expects($this->once())->method('update')->willReturnCallback(static function (Person $updated) use ($person, $impression): void {
            self::assertSame($person, $updated);
            self::assertSame(Rank::FirstRank, $updated->current_rank);
            self::assertSame(42, $updated->updated->by);
            self::assertTrue($updated->updated->at->equalTo($impression->at));
            self::assertCount(1, $updated->rankHistoryToPersist());
        });

        new StandardEventPersonRankUpdater($operations, $persons, $facts, new RankCalculator())
            ->update($event, $impression);
    }

    private function updater(ProtocolLineOperations $operations, RankFactsCollector $facts): StandardEventPersonRankUpdater
    {
        return new StandardEventPersonRankUpdater(
            $operations,
            $this->app->make(PersonRepository::class),
            $facts,
            new RankCalculator(),
        );
    }
}
