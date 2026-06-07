<?php

declare(strict_types=1);

namespace Tests\Application\Service\Club;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\ClubSearchDto;
use App\Application\Dto\Club\ViewClubDto;
use App\Application\Service\Club\ListClubs;
use App\Application\Service\Club\ListClubsService;
use App\Domain\Club\Club;
use App\Domain\Club\ClubRepository;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListClubsServiceTest extends TestCase
{
    private ListClubsService $service;

    private ClubRepository&MockObject $clubs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListClubsService(
            $this->clubs = $this->createMock(ClubRepository::class),
            new ClubAssembler(new AuthAssembler),
        );
    }

    #[Test]
    public function it_gets_list_of_clubs(): void
    {
        $clubs = Club::factory(count: 2)->make();

        $this->clubs
            ->expects($this->once())
            ->method('byCriteria')
            ->with(Criteria::empty())
            ->willReturn($clubs)
        ;

        $result = $this->service->execute(new ListClubs(new ClubSearchDto()));

        $this->assertContainsOnlyInstancesOf(ViewClubDto::class, $result);
    }

    #[Test]
    public function it_filters_clubs_by_ids(): void
    {
        $clubs = Club::factory(count: 2)->make();

        $this->clubs
            ->expects($this->once())
            ->method('byCriteria')
            ->with(new Criteria(['ids' => [1, 2]]))
            ->willReturn($clubs)
        ;

        $result = $this->service->execute(new ListClubs(new ClubSearchDto(ids: [1, 2])));

        $this->assertContainsOnlyInstancesOf(ViewClubDto::class, $result);
    }
}
