<?php

declare(strict_types=1);

namespace Tests\Application\Service\Rank;

use App\Application\Dto\Rank\RankAssembler;
use App\Application\Dto\Rank\UpdateActivationDto;
use App\Application\Dto\Rank\ViewRankDto;
use App\Application\Service\Rank\Exception\ProtocolLineNotFound;
use App\Application\Service\Rank\Exception\RankNotFound;
use App\Application\Service\Rank\UpdateRankActivationDate;
use App\Application\Service\Rank\UpdateRankActivationDateService;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankRepository;
use App\Domain\Shared\DummyTransactional;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class UpdateRankActivationDateServiceTest extends TestCase
{
    private UpdateRankActivationDateService $service;

    private MockObject&RankRepository $ranks;

    private MockObject&ProtocolLineRepository $protocolLines;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UpdateRankActivationDateService(
            $this->ranks = $this->createMock(RankRepository::class),
            $this->protocolLines = $this->createMock(ProtocolLineRepository::class),
            new DummyTransactional,
            new RankAssembler($this->protocolLines),
        );
    }

    #[Test]
    public function it_fails_when_rank_not_found(): void
    {
        $this->expectException(RankNotFound::class);

        $this->ranks->expects($this->once())->method('byId')->with(1)->willReturn(null);
        // до транзакции с протокол-линиями не доходим
        $this->protocolLines->expects($this->never())->method($this->anything());

        $this->service->execute(new UpdateRankActivationDate('1', new UpdateActivationDto()));
    }

    #[Test]
    public function it_fails_when_protocol_line_not_found(): void
    {
        $this->expectException(ProtocolLineNotFound::class);

        /** @var Rank $rank */
        $rank = Rank::factory()->makeOne(['id' => 5, 'event_id' => null]);
        $this->ranks->expects($this->once())->method('byId')->with(5)->willReturn($rank);

        $this->protocolLines->expects($this->once())->method('lockOneByCriteria')->willReturn(null);
        // протокол-линия не найдена → обновления быть не должно
        $this->protocolLines->expects($this->never())->method('update');

        $this->service->execute(new UpdateRankActivationDate('5', new UpdateActivationDto()));
    }

    #[Test]
    public function it_activates_protocol_line_and_returns_dto(): void
    {
        $person = Person::factory()->makeOne(['id' => 1, 'firstname' => 'Иван', 'lastname' => 'Иванов']);

        /** @var Rank $rank */
        $rank = Rank::factory()->makeOne(['id' => 5, 'event_id' => null]);
        $rank->setRelation('person', $person);
        $rank->setRelation('event', null);

        /** @var ProtocolLine $protocolLine */
        $protocolLine = ProtocolLine::factory()->makeOne();

        $this->ranks->expects($this->once())->method('byId')->with(5)->willReturn($rank);
        $this->protocolLines->expects($this->once())->method('lockOneByCriteria')->willReturn($protocolLine);
        $this->protocolLines->expects($this->once())->method('update')->with($protocolLine);

        $dto = new UpdateActivationDto();
        $dto->date = '2023-06-01';

        $result = $this->service->execute(new UpdateRankActivationDate('5', $dto));

        $this->assertInstanceOf(ViewRankDto::class, $result);
        // дата активации применена к протокол-линии
        $this->assertSame('2023-06-01', $protocolLine->activate_rank->toDateString());
    }
}
