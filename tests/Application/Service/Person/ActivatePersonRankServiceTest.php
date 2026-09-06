<?php

declare(strict_types=1);

namespace Tests\Application\Service\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\ActivatePersonRankDto;
use App\Application\Dto\ProtocolLine\ProtocolLineAssembler;
use App\Application\Dto\ProtocolLine\ViewProtocolLineDto;
use App\Application\Service\Person\ActivatePersonRank;
use App\Application\Service\Person\ActivatePersonRankService;
use App\Application\Service\Person\Exception\ProtocolLineNotFound;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ActivatePersonRankServiceTest extends TestCase
{
    #[Test]
    public function it_fails_when_protocol_line_is_not_found(): void
    {
        $repository = $this->createMock(ProtocolLineRepository::class);
        $repository->expects($this->once())->method('lockById')->with(7)->willReturn(null);

        $service = new ActivatePersonRankService(
            $repository,
            new FrozenClock(Carbon::parse('2026-09-03')),
            new DummyTransactional(),
            new ProtocolLineAssembler(),
        );

        $this->expectException(ProtocolLineNotFound::class);
        $service->execute($this->command());
    }

    #[Test]
    public function it_activates_rank_and_returns_protocol_line(): void
    {
        /** @var ProtocolLine&MockObject $line */
        $line = $this->createMock(ProtocolLine::class);
        $line->expects($this->atLeast(9))->method('__get')->willReturnMap([
            ['id', 7],
            ['person_id', 42],
            ['firstname', 'Jane'],
            ['lastname', 'Doe'],
            ['distance_id', 11],
            ['year', null],
            ['time', null],
            ['place', null],
            ['complete_rank', null],
        ]);
        $line->method('relationLoaded')->willReturn(false);
        $line->expects($this->once())
            ->method('activateRank')
            ->with(
                $this->callback(function (Carbon $date): bool {
                    $this->assertSame('2026-09-10', $date->toDateString());

                    return true;
                }),
                $this->anything(),
            );

        $repository = $this->createMock(ProtocolLineRepository::class);
        $repository->expects($this->once())->method('lockById')->with(7)->willReturn($line);
        $repository->expects($this->once())->method('update')->with($line);

        $service = new ActivatePersonRankService(
            $repository,
            new FrozenClock(Carbon::parse('2026-09-03')),
            new DummyTransactional(),
            new ProtocolLineAssembler(),
        );

        $result = $service->execute($this->command());

        $this->assertInstanceOf(ViewProtocolLineDto::class, $result);
        $this->assertSame('42', $result->personId);
    }

    private function command(): ActivatePersonRank
    {
        $dto = new ActivatePersonRankDto();
        $dto->date = '2026-09-10';

        return new ActivatePersonRank('7', $dto, new UserId(5));
    }
}
