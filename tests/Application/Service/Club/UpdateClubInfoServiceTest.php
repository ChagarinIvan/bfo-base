<?php

declare(strict_types=1);

namespace Tests\Application\Service\Club;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Club\ClubAssembler;
use App\Application\Dto\Club\ClubDto;
use App\Application\Service\Club\Exception\ClubNotFound;
use App\Application\Service\Club\Exception\FailedToUpdateClub;
use App\Application\Service\Club\UpdateClubInfo;
use App\Application\Service\Club\UpdateClubInfoService;
use App\Domain\Auth\Impression;
use App\Domain\Club\Club;
use App\Domain\Club\ClubInput;
use App\Domain\Club\ClubNameNormalizer;
use App\Domain\Club\ClubRepository;
use App\Domain\Club\ClubUpdater;
use App\Domain\Club\Exception\ClubAlreadyExist;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\SymbolNormalizer;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class UpdateClubInfoServiceTest extends TestCase
{
    private ClubRepository&MockObject $clubs;

    private ClubUpdater&MockObject $updater;

    private UpdateClubInfoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UpdateClubInfoService(
            $this->clubs = $this->createMock(ClubRepository::class),
            $this->updater = $this->createMock(ClubUpdater::class),
            new ClubNameNormalizer(new SymbolNormalizer),
            new ClubAssembler(new AuthAssembler),
            new DummyTransactional,
        );
    }

    #[Test]
    public function it_fails_when_club_is_not_found(): void
    {
        $this->expectException(ClubNotFound::class);
        $this->clubs->expects($this->once())->method('lockById')->with(1)->willReturn(null);
        $this->updater->expects($this->never())->method('update');

        $this->service->execute($this->command('Новы клуб'));
    }

    #[Test]
    public function it_rejects_duplicate_name_excluding_current_club(): void
    {
        $club = $this->createStub(Club::class);
        $this->clubs->expects($this->once())->method('lockById')->with(1)->willReturn($club);
        $this->updater->expects($this->once())->method('update')->with($club, $this->anything())
            ->willThrowException(new ClubAlreadyExist('Duplicate'));

        $this->expectException(FailedToUpdateClub::class);
        $this->service->execute($this->command('Новы клуб'));
    }

    #[Test]
    public function it_updates_club_and_calls_repository(): void
    {
        $club = $this->clubMock();
        $this->clubs->expects($this->once())->method('lockById')->with(1)->willReturn($club);
        $this->updater->expects($this->once())->method('update')->with(
            $club,
            $this->isInstanceOf(ClubInput::class),
        )->willReturn($club);
        $this->clubs->expects($this->once())->method('update')->with($club);

        $this->service->execute($this->command('Новы клуб'));
    }

    #[Test]
    public function it_returns_current_club_without_updating_when_info_is_unchanged(): void
    {
        $club = $this->clubMock('Новы клуб', 'новы клуб');
        $this->clubs->expects($this->once())->method('lockById')->with(1)->willReturn($club);
        $this->updater->expects($this->never())->method('update');
        $this->clubs->expects($this->never())->method('update');

        $result = $this->service->execute($this->command('Новы клуб'));

        $this->assertSame('Новы клуб', $result->name);
    }

    private function command(string $name): UpdateClubInfo
    {
        $dto = new ClubDto();
        $dto->name = $name;

        return new UpdateClubInfo('1', $dto, new UserId(1));
    }

    private function clubMock(string $name = 'New name', string $normalizedName = 'new name'): Club
    {
        $club = $this->createStub(Club::class);
        $club->method('__get')->willReturnMap([
            ['id', 1], ['name', $name], ['normalize_name', $normalizedName], ['persons_count', 0],
            ['created', new Impression(new Carbon('2026-01-01'), 1)],
            ['updated', new Impression(new Carbon('2026-01-01'), 1)],
        ]);
        $club->method('__isset')->willReturnMap([['persons_count', true]]);

        return $club;
    }
}
