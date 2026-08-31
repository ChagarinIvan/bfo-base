<?php

declare(strict_types=1);

namespace Tests\Application\Service\Club;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\SearchClubDto;
use App\Application\Service\Club\ListClubs;
use App\Application\Service\Club\ListClubsService;
use App\Domain\Club\Club;
use App\Domain\Club\ClubRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Pagerfanta\Adapter\ArrayAdapter;
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
    public function it_returns_a_paginated_club_slice(): void
    {
        $club = Club::factory()->makeOne([
            'id' => 42,
            'name' => 'Minsk Orienteering',
            'persons_count' => 7,
        ]);

        $this->clubs
            ->expects($this->once())
            ->method('paginate')
            ->with(new Criteria(['name' => 'Minsk Orienteering']))
            ->willReturn(new Slice(new ArrayAdapter([$club])))
        ;

        $result = $this->service->execute(
            new ListClubs(new SearchClubDto(name: '  Minsk Orienteering  ')),
        );

        $this->assertInstanceOf(Slice::class, $result);
        $items = $result->items();
        $this->assertSame('42', $items[0]->id);
        $this->assertSame(7, $items[0]->personsCount);
    }

    #[Test]
    public function it_omits_an_empty_name_filter(): void
    {
        $this->clubs
            ->expects($this->once())
            ->method('paginate')
            ->with(Criteria::empty())
            ->willReturn(new Slice(new ArrayAdapter([])))
        ;

        $result = $this->service->execute(new ListClubs(new SearchClubDto(name: '  ')));

        $this->assertCount(0, $result);
    }
}
