<?php

declare(strict_types=1);

namespace Tests\Application\Service\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\UpdatePersonRankActivationDateDto;
use App\Application\Service\Person\Exception\ProtocolLineNotFound;
use App\Application\Service\Person\UpdatePersonRankActivationDate;
use App\Application\Service\Person\UpdatePersonRankActivationDateService;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class UpdatePersonRankActivationDateServiceTest extends TestCase
{
    #[Test]
    public function it_fails_when_protocol_line_is_not_found(): void
    {
        $repository = $this->createMock(ProtocolLineRepository::class);
        $repository->expects($this->once())->method('lockById')->with(7)->willReturn(null);

        $service = new UpdatePersonRankActivationDateService(
            $repository,
            new FrozenClock(Carbon::parse('2026-09-03')),
            new DummyTransactional(),
        );

        $this->expectException(ProtocolLineNotFound::class);
        $service->execute($this->command('2026-09-10'));
    }

    #[Test]
    public function it_updates_activation_date_and_returns_person_id(): void
    {
        /** @var ProtocolLine&MockObject $line */
        $line = $this->createMock(ProtocolLine::class);
        $line->expects($this->once())->method('__get')->with('person_id')->willReturn(42);
        $line->expects($this->once())
            ->method('activateRank')
            ->with(
                $this->callback(function (?Carbon $date): bool {
                    $this->assertSame('2026-09-10', $date?->toDateString());

                    return true;
                }),
                $this->anything(),
            );

        $repository = $this->createMock(ProtocolLineRepository::class);
        $repository->expects($this->once())->method('lockById')->with(7)->willReturn($line);
        $repository->expects($this->once())->method('update')->with($line);

        $service = new UpdatePersonRankActivationDateService(
            $repository,
            new FrozenClock(Carbon::parse('2026-09-03')),
            new DummyTransactional(),
        );

        $this->assertSame(42, $service->execute($this->command('2026-09-10')));
    }

    private function command(?string $date): UpdatePersonRankActivationDate
    {
        $dto = new UpdatePersonRankActivationDateDto();
        $dto->date = $date;

        return new UpdatePersonRankActivationDate('7', $dto, new UserId(5));
    }
}
