<?php

declare(strict_types=1);

namespace Tests\Application\Service\Club;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\ViewClubDto;
use App\Application\Service\Club\Exception\ClubNotFound;
use App\Application\Service\Club\ViewClub;
use App\Application\Service\Club\ViewClubService;
use App\Domain\Club\Club;
use App\Domain\Club\ClubRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ViewClubServiceTest extends TestCase
{
    private ViewClubService $service;

    private ClubRepository&MockObject $clubs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ViewClubService(
            $this->clubs = $this->createMock(ClubRepository::class),
            new ClubAssembler(new AuthAssembler),
        );
    }

    /** @test */
    public function it_fails_when_club_not_found(): void
    {
        $this->expectException(ClubNotFound::class);

        $this->clubs
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $command = new ViewClub('1');
        $this->service->execute($command);
    }

    /** @test */
    public function it_shows_club(): void
    {
        /** @var Club $club */
        $club = Club::factory()->makeOne();

        $this->clubs
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($club)
        ;

        $command = new ViewClub('1');
        $result = $this->service->execute($command);

        $this->assertInstanceOf(ViewClubDto::class, $result);
        $this->assertEquals($club->id, $result->id);
    }
}
