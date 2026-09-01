<?php

declare(strict_types=1);

namespace Tests\Domain\Club\Factory;

use App\Domain\Club\Club;
use App\Domain\Club\ClubInfo;
use App\Domain\Club\ClubInput;
use App\Domain\Club\ClubRepository;
use App\Domain\Club\Exception\ClubAlreadyExist;
use App\Domain\Club\Factory\ClubFactory;
use App\Domain\Club\Factory\PreventDuplicateClubFactory;
use PHPUnit\Framework\Attributes\Test;
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

    #[Test]
    public function it_fails_when_club_with_same_name_already_exists(): void
    {
        $this->expectException(ClubAlreadyExist::class);
        $this->expectExceptionMessageIsOrContains('Club with name "Тэст клуб" already exist.');

        $this->decorated->expects($this->never())->method('create');
        $this->clubs
            ->expects($this->once())
            ->method('oneByNormalizedName')
            ->with('тэст клуб')
            ->willReturn($this->createStub(Club::class))
        ;

        $this->factory->create(new ClubInput(new ClubInfo('Тэст клуб', 'тэст клуб'), 1));
    }

    #[Test]
    public function it_propagates_club_creation_on_equal_club_not_exists(): void
    {
        $input = new ClubInput(new ClubInfo('Тэст клуб', 'тэст клуб'), 1);

        $this->decorated
            ->expects($this->once())
            ->method('create')
            ->with($this->identicalTo($input))
            ->willReturn($this->createStub(Club::class))
        ;

        $this->clubs
            ->expects($this->once())
            ->method('oneByNormalizedName')
            ->with('тэст клуб')
            ->willReturn(null)
        ;

        $this->factory->create($input);
    }
}
