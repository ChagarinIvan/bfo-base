<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\CupDto;
use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\UpdateCup;
use App\Application\Service\Cup\UpdateCupService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupRepository;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class UpdateCupServiceTest extends TestCase
{
    private UpdateCupService $service;

    private CupRepository&MockObject $cups;

    private EventRepository&MockObject $events;

    protected function setUp(): void
    {
        parent::setUp();

        $authAssembler = new AuthAssembler;

        $this->service = new UpdateCupService(
            $this->cups = $this->createMock(CupRepository::class),
            new FrozenClock(Carbon::parse('2023-04-01')),
            new CupAssembler(
                $this->events = $this->createMock(EventRepository::class),
                $authAssembler,
                new CupEventAssembler(new EventAssembler($authAssembler)),
            ),
            new DummyTransactional,
        );
    }

    /** @test */
    public function it_fails_when_cup_not_found(): void
    {
        $this->expectException(CupNotFound::class);

        $this->cups
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $dto = new CupDto();
        $dto->name = 'test cup';
        $dto->eventsCount = 4;
        $dto->year = 2023;
        $dto->type = 'master';
        $dto->visible = false;

        $command = new UpdateCup('1', $dto, new UserId(1));
        $this->service->execute($command);
    }

    /** @test */
    public function it_updates_cup(): void
    {
        /** @var Cup $cup */
        $cup = Cup::factory()->makeOne();
        /** @var Event $event */
        $event = Event::factory()->makeOne();

        $this->cups
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn($cup)
        ;

        $this->events
            ->expects($this->once())
            ->method('oneByCriteria')
            ->with($this->equalTo( new Criteria(['cupId' => $cup->id], ['date' => 'desc'])))
            ->willReturn($event)
        ;

        $this->cups->expects($this->once())->method('update');

        $dto = new CupDto();
        $dto->name = 'test cup';
        $dto->eventsCount = 4;
        $dto->year = 2023;
        $dto->type = 'master';
        $dto->visible = false;

        $command = new UpdateCup('1', $dto, new UserId(1));
        $cup = $this->service->execute($command);

        $this->assertEquals('test cup', $cup->name);
        $this->assertEquals('2023', $cup->year);
        $this->assertEquals('4', $cup->eventsCount);
        $this->assertEquals('master', $cup->type);
        $this->assertEquals('2023-04-01T00:00:00+00:00', $cup->updated->at);
    }
}
