<?php

declare(strict_types=1);

namespace Tests\Domain\Club\Factory;

use App\Domain\Club\Club;
use App\Domain\Club\ClubRepository;
use App\Domain\Club\Exception\ClubAlreadyExist;
use App\Domain\Club\Factory\ClubFactory;
use App\Domain\Club\Factory\ClubInput;
use App\Domain\Club\Factory\PreventDuplicateClubFactory;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class PreventDuplicateClubFactoryTest extends TestCase
{
    private ClubFactory&MockObject $decorated;

    private ClubRepository&MockObject $clubs;

    private PreventDuplicateClubFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new PreventDuplicateClubFactory(
            $this->decorated = $this->createMock(ClubFactory::class),
            $this->clubs = $this->createMock(ClubRepository::class),
        );
    }

    /** @test */
    public function it_fails_when_club_with_same_name_already_exists(): void
    {
        $this->expectException(ClubAlreadyExist::class);
        $this->expectExceptionMessage('Club with name "test club" already exist.');

        $this->decorated->expects($this->never())->method('create');
        $this->clubs
            ->expects($this->once())
            ->method('oneByCriteria')
            ->with($this->equalTo(new Criteria(['name' => 'test club'])))
            ->willReturn(Club::factory()->makeOne())
        ;

        $this->factory->create(new ClubInput('test club', 1));
    }

    /** @test */
    public function it_propagates_club_creation_on_equal_club_not_exists(): void
    {
        $input = new ClubInput('test club', 1);

        $this->decorated
            ->expects($this->once())
            ->method('create')
            ->with($this->identicalTo($input))
            ->willReturn(Club::factory()->makeOne())
        ;

        $this->clubs
            ->expects($this->once())
            ->method('oneByCriteria')
            ->with($this->equalTo(new Criteria(['name' => 'test club'])))
            ->willReturn(null)
        ;

        $this->factory->create($input);
    }
}
