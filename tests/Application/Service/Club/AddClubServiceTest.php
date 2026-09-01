<?php

declare(strict_types=1);

namespace Tests\Application\Service\Club;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\ClubDto;
use App\Application\Service\Club\AddClub;
use App\Application\Service\Club\AddClubService;
use App\Application\Service\Club\Exception\FailedToAddClub;
use App\Domain\Auth\Impression;
use App\Domain\Club\Club;
use App\Domain\Club\ClubInfo;
use App\Domain\Club\ClubInput;
use App\Domain\Club\ClubRepository;
use App\Domain\Club\Exception\ClubAlreadyExist;
use App\Domain\Club\Factory\ClubFactory;
use App\Domain\Club\Factory\ClubNameNormalizer;
use App\Domain\Shared\SymbolNormalizer;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class AddClubServiceTest extends TestCase
{
    private AddClubService $service;

    private ClubFactory&MockObject $factory;

    private ClubRepository&MockObject $clubs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AddClubService(
            $this->factory = $this->createMock(ClubFactory::class),
            $this->clubs = $this->createMock(ClubRepository::class),
            new ClubNameNormalizer(new SymbolNormalizer),
            new ClubAssembler(new AuthAssembler),
        );
    }

    #[Test]
    public function it_fails_on_exists_duplicate_club(): void
    {
        $this->expectException(FailedToAddClub::class);
        $this->expectExceptionMessageIsOrContains('Unable to add club. Reason: Error.');

        $input = new ClubInput(new ClubInfo('Тэст клуб', 'тэст клуб'), 1);
        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($input)
            ->willThrowException(new ClubAlreadyExist('Error.'))
        ;

        $this->clubs->expects($this->never())->method('add');

        $dto = new ClubDto();
        $dto->name = 'Тэст клуб';

        $command = new AddClub($dto, new UserId(1));
        $this->service->execute($command);
    }

    #[Test]
    public function it_creates_club(): void
    {
        $input = new ClubInput(new ClubInfo('Тэст клуб', 'тэст клуб'), 1);
        $club = $this->createStub(Club::class);
        $club->method('__get')->willReturnMap([
            ['id', 42],
            ['name', 'Тэст клуб'],
            ['persons_count', 0],
            ['created', new Impression(new Carbon('2026-01-01'), 1)],
            ['updated', new Impression(new Carbon('2026-01-01'), 1)],
        ]);
        $club->method('__isset')->willReturnMap([['persons_count', true]]);

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($input)
            ->willReturn($club)
        ;

        $this->clubs
            ->expects($this->once())
            ->method('add')
            ->with($this->identicalTo($club))
        ;

        $dto = new ClubDto();
        $dto->name = 'Тэст клуб';

        $command = new AddClub($dto, new UserId(1));
        $clubDto = $this->service->execute($command);

        $this->assertEquals($club->id, $clubDto->id);
    }
}
