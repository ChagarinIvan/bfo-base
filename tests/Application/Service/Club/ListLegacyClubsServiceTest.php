<?php

declare(strict_types=1);

namespace Tests\Application\Service\Club;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\LegacySearchClubDto;
use App\Application\Service\Club\ListLegacyClubs;
use App\Application\Service\Club\ListLegacyClubsService;
use App\Domain\Auth\Impression;
use App\Domain\Club\Club;
use App\Domain\Club\ClubRepository;
use App\Domain\Shared\Criteria;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

/** @see ListLegacyClubsService */
final class ListLegacyClubsServiceTest extends TestCase
{
    private ListLegacyClubsService $service;

    private ClubRepository&MockObject $clubs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListLegacyClubsService(
            $this->clubs = $this->createMock(ClubRepository::class),
            new ClubAssembler(new AuthAssembler),
        );
    }

    #[Test]
    public function it_gets_list_of_clubs(): void
    {
        $clubs = new Collection([$this->clubStub(), $this->clubStub()]);

        $this->clubs
            ->expects($this->once())
            ->method('byCriteria')
            ->with(Criteria::empty())
            ->willReturn($clubs)
        ;

        $result = $this->service->execute(new ListLegacyClubs(new LegacySearchClubDto()));

        $this->assertCount(2, $result);
    }

    #[Test]
    public function it_filters_clubs_by_ids(): void
    {
        $clubs = new Collection([$this->clubStub(), $this->clubStub()]);

        $this->clubs
            ->expects($this->once())
            ->method('byCriteria')
            ->with(new Criteria(['ids' => [1, 2]]))
            ->willReturn($clubs)
        ;

        $result = $this->service->execute(new ListLegacyClubs(new LegacySearchClubDto(ids: [1, 2])));

        $this->assertCount(2, $result);
    }

    private function clubStub(): Club
    {
        $club = $this->createStub(Club::class);
        $impression = new Impression(new Carbon('2026-01-01'), 1);
        $club->method('__get')->willReturnMap([
            ['id', 1], ['name', 'Клуб'], ['persons_count', 0],
            ['created', $impression], ['updated', $impression],
        ]);

        return $club;
    }
}
