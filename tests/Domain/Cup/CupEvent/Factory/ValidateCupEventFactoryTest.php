<?php

declare(strict_types=1);

namespace Tests\Domain\Cup\CupEvent\Factory;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\Exception\CupAlreadyContainsEvent;
use App\Domain\Cup\CupEvent\Factory\CupEventFactory;
use App\Domain\Cup\CupEvent\Factory\CupEventInput;
use App\Domain\Cup\CupEvent\Factory\ValidateCupEventFactory;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Exception\CupNotExists;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Event\Exception\EventNotExists;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ValidateCupEventFactoryTest extends TestCase
{
    private CupEventFactory&MockObject $decorated;

    private CupRepository&MockObject $cups;

    private EventRepository&MockObject $events;

    private CupEventRepository&MockObject $cupEvents;

    private ValidateCupEventFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new ValidateCupEventFactory(
            $this->decorated = $this->createMock(CupEventFactory::class),
            $this->cups = $this->createMock(CupRepository::class),
            $this->events = $this->createMock(EventRepository::class),
            $this->cupEvents = $this->createMock(CupEventRepository::class),
        );
    }

    #[Test]
    public function it_rejects_a_missing_cup_before_other_lookups(): void
    {
        $this->expectException(CupNotExists::class);
        $this->cups->expects($this->once())->method('byId')->with(1)->willReturn(null);
        $this->events->expects($this->never())->method('byId');

        $this->factory->create($this->input());
    }

    #[Test]
    public function it_rejects_a_missing_event(): void
    {
        $this->cups->expects($this->once())->method('byId')->with(1)->willReturn($this->createStub(Cup::class));
        $this->events->expects($this->once())->method('byId')->with(2)->willReturn(null);

        $this->expectException(EventNotExists::class);
        $this->factory->create($this->input());
    }

    #[Test]
    public function it_rejects_a_duplicate_stage_for_the_same_cup_and_event(): void
    {
        $this->cups->method('byId')->willReturn($this->createStub(Cup::class));
        $this->events->method('byId')->willReturn($this->createStub(Event::class));
        $this->cupEvents
            ->expects($this->once())
            ->method('byCriteria')
            ->willReturnCallback(function (Criteria $criteria): Collection {
                $this->assertSame('1', $criteria->param('cupId'));
                $this->assertSame(['2'], $criteria->param('eventIds'));

                return new Collection([$this->createStub(CupEvent::class)]);
            })
        ;

        $this->expectException(CupAlreadyContainsEvent::class);
        $this->factory->create($this->input());
    }

    #[Test]
    public function it_delegates_valid_input_to_the_standard_factory(): void
    {
        $input = $this->input();
        $cupEvent = $this->createStub(CupEvent::class);
        $this->cups->method('byId')->willReturn($this->createStub(Cup::class));
        $this->events->method('byId')->willReturn($this->createStub(Event::class));
        $this->cupEvents->method('byCriteria')->willReturn(new Collection());
        $this->decorated->expects($this->once())->method('create')->with($input)->willReturn($cupEvent);

        $this->assertSame($cupEvent, $this->factory->create($input));
    }

    private function input(): CupEventInput
    {
        return new CupEventInput(1, 2, 100.0, 3);
    }
}
