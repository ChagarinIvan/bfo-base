<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Cup\CupAssembler;
use App\Application\Dto\Cup\CupSearchDto;
use App\Application\Dto\Cup\ViewCupDto;
use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Service\Cup\ListCup;
use App\Application\Service\Cup\ListCupService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupRepository;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListCupServiceTest extends TestCase
{
    private ListCupService $service;

    private CupRepository&MockObject $cups;

    private EventRepository&MockObject $events;

    protected function setUp(): void
    {
        parent::setUp();
        $this->events = $this->createMock(EventRepository::class);
        $authAssembler = new AuthAssembler;

        $this->service = new ListCupService(
            $this->cups = $this->createMock(CupRepository::class),
            new CupAssembler($this->events, $authAssembler, new CupEventAssembler(new EventAssembler($authAssembler)))
        );
    }

    /** @test */
    public function it_gets_list_of_competitions(): void
    {
        /** @var Cup[] $cups */
        $cups = Cup::factory(count: 2)->make();

        $this->cups
            ->expects($this->once())
            ->method('byCriteria')
            ->with($this->equalTo(new Criteria(['year' => '2021'])))
            ->willReturn($cups)
        ;

        $dto = new CupSearchDto('2021');

        $command = new ListCup($dto);
        $result = $this->service->execute($command);

        $this->assertIsList($result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ViewCupDto::class, $result);
        $this->assertEquals($cups[1]->id, $result[1]->id);
    }
}
