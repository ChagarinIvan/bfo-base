<?php

declare(strict_types=1);

namespace Tests\Application\Service\Rank;

use App\Application\Dto\Rank\RankAssembler;
use App\Application\Dto\Rank\ViewRankDto;
use App\Application\Service\Rank\PersonRanks;
use App\Application\Service\Rank\PersonRanksService;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankRepository;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class PersonRanksServiceTest extends TestCase
{
    private PersonRanksService $service;

    private MockObject&RankRepository $ranks;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PersonRanksService(
            $this->ranks = $this->createMock(RankRepository::class),
            new RankAssembler($this->createStub(ProtocolLineRepository::class)),
        );
    }

    #[Test]
    public function it_returns_empty_list_when_person_has_no_ranks(): void
    {
        $this->ranks
            ->expects($this->once())
            ->method('byCriteria')
            ->willReturn(new Collection())
        ;

        $result = $this->service->execute(new PersonRanks('1'));

        $this->assertSame([], $result);
    }

    #[Test]
    public function it_assembles_dtos_for_each_person_rank(): void
    {
        $person = Person::factory()->makeOne(['id' => 1, 'firstname' => 'Иван', 'lastname' => 'Иванов']);

        $ranks = Rank::factory()->count(2)->make(['person_id' => 1, 'event_id' => null]);
        foreach ($ranks as $rank) {
            $rank->setRelation('person', $person);
            $rank->setRelation('event', null);
        }

        $this->ranks
            ->expects($this->once())
            ->method('byCriteria')
            ->willReturn($ranks)
        ;

        $result = $this->service->execute(new PersonRanks('1'));

        $this->assertCount(2, $result);
        $this->assertInstanceOf(ViewRankDto::class, $result[0]);
        $this->assertSame('1', $result[0]->personId);
        $this->assertSame('Иван', $result[0]->personFirstname);
    }
}
