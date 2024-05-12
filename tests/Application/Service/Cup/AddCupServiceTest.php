<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\CupDto;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Service\Cup\AddCup;
use App\Application\Service\Cup\AddCupService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupInfo;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\CupType;
use App\Domain\Cup\Factory\CupFactory;
use App\Domain\Cup\Factory\CupInput;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use App\Models\Year;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class AddCupServiceTest extends TestCase
{
    private AddCupService $service;

    private CupFactory&MockObject $factory;

    private CupRepository&MockObject $cups;

    private EventRepository&MockObject $events;

    protected function setUp(): void
    {
        parent::setUp();

        $authAssembler = new AuthAssembler;
        $this->service = new AddCupService(
            $this->factory = $this->createMock(CupFactory::class),
            $this->cups = $this->createMock(CupRepository::class),
            new CupAssembler(
                $this->events = $this->createMock(EventRepository::class),
                new EventAssembler($authAssembler),
                $authAssembler,
            ),
        );
    }

    /** @test */
    public function it_creates_cup(): void
    {
        $input = new CupInput(
            new CupInfo(
                name: 'test cup',
                eventsCount: 2,
                year: Year::y2024,
                type: CupType::YOUTH,
            ),
            visible: true,
            userId: 1,
        );

        /** @var Cup $cup */
        $cup = Cup::factory()->makeOne();
        /** @var Event $event */
        $event = Event::factory()->makeOne();

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($input))
            ->willReturn($cup)
        ;

        $this->cups
            ->expects($this->once())
            ->method('add')
            ->with($this->identicalTo($cup))
        ;

        $this->events
            ->expects($this->once())
            ->method('oneByCriteria')
            ->with($this->equalTo( new Criteria(['cupId' => $cup->id], ['date' => 'desc'])))
            ->willReturn($event)
        ;

        $dto = new CupDto();
        $dto->name = 'test cup';
        $dto->eventsCount = 2;
        $dto->year = 2024;
        $dto->type = 'youth';
        $dto->visible = true;

        $command = new AddCup($dto, new UserId(1));
        $cupDto = $this->service->execute($command);

        $this->assertEquals($cup->id, $cupDto->id);
        $this->assertEquals($cup->id, $cupDto->id);
    }
}
