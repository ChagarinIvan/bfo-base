<?php

declare(strict_types=1);

namespace Tests\Domain\Event\Factory;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Event\Factory\EventFactory;
use App\Domain\Event\Factory\EventInput;
use App\Domain\Event\Factory\UniteFactory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UniteFactoryTest extends TestCase
{
    #[Test]
    public function it_creates_a_combined_event_with_the_standard_factory(): void
    {
        $first = $this->sourceEvent('First');
        $second = $this->sourceEvent('Second');
        $newEvent = new Event;
        $input = null;
        $factory = $this->createMock(EventFactory::class);
        $factory->expects($this->once())
            ->method('create')
            ->with($this->isInstanceOf(EventInput::class), null)
            ->willReturnCallback(static function (EventInput $eventInput) use (&$input, $newEvent): Event {
                $input = $eventInput;

                return $newEvent;
            });

        $uniteFactory = new UniteFactory($factory);

        $result = $uniteFactory->create(
            new Collection([$first, $second]),
            9,
            new Impression(Carbon::parse('2026-05-11'), 4),
        );

        $this->assertSame($newEvent, $result);
        $this->assertInstanceOf(EventInput::class, $input);
        $this->assertSame('First + Second', $input->info->name);
        $this->assertSame(9, $input->competitionId);
        $this->assertSame(4, $input->userId);
    }

    private function sourceEvent(string $name): Event
    {
        $event = new Event;
        $event->name = $name;
        $event->date = Carbon::parse('2026-05-10');
        $event->setRelation('protocolLines', new Collection);

        return $event;
    }
}
