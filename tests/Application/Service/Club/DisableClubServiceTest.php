<?php

declare(strict_types=1);

namespace Tests\Application\Service\Club;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Club\DisableClub;
use App\Application\Service\Club\DisableClubService;
use App\Application\Service\Club\Exception\ClubNotFound;
use App\Domain\Club\Club;
use App\Domain\Club\ClubRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class DisableClubServiceTest extends TestCase
{
    private DisableClubService $service;

    private ClubRepository&MockObject $clubs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DisableClubService(
            $this->clubs = $this->createMock(ClubRepository::class),
            new FrozenClock(),
            new DummyTransactional(),
        );
    }

    /** @test */
    public function it_fails_when_club_not_found(): void
    {
        $this->expectException(ClubNotFound::class);

        $this->clubs
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $command = new DisableClub('1', new UserId(1));
        $this->service->execute($command);
    }

    /** @test */
    public function it_disables_club(): void
    {
        /** @var Club $club */
        $club = Club::factory()->makeOne();

        $this->clubs
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn($club)
        ;

        $this->clubs
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($club))
        ;

        $command = new DisableClub('1', new UserId(1));
        $this->service->execute($command);

        $this->assertFalse($club->active);
    }
}
