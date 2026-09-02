<?php

declare(strict_types=1);

namespace Tests\Application\Handler\Event;

use App\Application\Handler\Event\DisableEventHandler;
use App\Application\Service\Cup\ClearCupCacheService;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Auth\Impression;
use App\Domain\Cup\CupCacheInvalidator;
use App\Domain\Event\Event;
use App\Domain\Event\Event\EventDisabled;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFactsCollector;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;
use App\Services\DistanceService;
use App\Services\ProtocolLineService;
use Carbon\Carbon;
use Closure;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DisableEventHandlerTest extends TestCase
{
    #[Test]
    public function it_rebuilds_people_affected_by_deleted_protocol_lines_with_the_event_impression(): void
    {
        $event = new Event();
        $event->name = 'Cup';
        $event->updated = $impression = new Impression(Carbon::parse('2026-09-02 12:00:00'), 7);
        $event->setRelation('cups', collect());

        $protocolLines = $this->createMock(ProtocolLineService::class);
        $protocolLines->expects($this->once())->method('personIdsForEvent')->with($event)->willReturn([12, 18]);
        $protocolLines->expects($this->once())->method('deleteEventLines')->with($event);

        $distances = $this->createMock(DistanceService::class);
        $distances->expects($this->once())->method('deleteEventDistances')->with($event);

        $firstPerson = $this->createMock(Person::class);
        $secondPerson = $this->createMock(Person::class);
        $firstPerson->expects($this->once())->method('updateRanks')->with($this->anything(), $impression);
        $secondPerson->expects($this->once())->method('updateRanks')->with($this->anything(), $impression);
        $persons = $this->createMock(PersonRepository::class);
        $persons->expects($this->exactly(2))->method('lockById')->willReturnMap([[12, $firstPerson], [18, $secondPerson]]);
        $persons->expects($this->exactly(2))->method('update');
        $facts = $this->createMock(RankFactsCollector::class);
        $facts->expects($this->exactly(2))->method('collect')->willReturn([]);
        $clock = $this->createMock(Clock::class);
        $clock->method('now')->willReturn(Carbon::parse('2026-09-02 12:00:00'));
        $transaction = $this->createMock(TransactionManager::class);
        $transaction->expects($this->exactly(2))->method('run')->willReturnCallback(static fn (Closure $callback): mixed => $callback());
        $rebuild = new RebuildPersonRanksService($persons, $facts, new RankCalculator(), $clock, $transaction);

        new DisableEventHandler($protocolLines, $distances, new ClearCupCacheService($this->createStub(CupCacheInvalidator::class)), $rebuild)
            ->handle(new EventDisabled($event));
    }
}
