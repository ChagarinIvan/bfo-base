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
use App\Domain\Auth\Impression;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_fails_when_club_not_found(): void
    {
        $this->expectException(ClubNotFound::class);

        $this->clubs
            ->expects($this->once())
            ->method('byId')
            ->with(1)
            ->willReturn(null)
        ;

        $command = new ViewClub('1');
        $this->service->execute($command);
    }

    #[Test]
    public function it_shows_club(): void
    {
        $club = $this->createStub(Club::class);
        $club->method('__get')->willReturnMap([
            ['id', 1],
            ['name', 'Club'],
            ['persons_count', 3],
            ['created', $this->impressionValue()],
            ['updated', $this->impressionValue()],
        ]);
        $club->method('__isset')->willReturnMap([['persons_count', true]]);

        $this->clubs
            ->expects($this->once())
            ->method('byId')
            ->with(1)
            ->willReturn($club)
        ;

        $command = new ViewClub('1');
        $result = $this->service->execute($command);

        $this->assertInstanceOf(ViewClubDto::class, $result);
        $this->assertSame('1', $result->id);
        $this->assertSame(3, $result->personsCount);
        $this->assertObjectNotHasProperty('normalizeName', $result);
    }

    private function impressionValue(): Impression
    {
        return new Impression(new Carbon('2026-01-01'), 1);
    }
}
