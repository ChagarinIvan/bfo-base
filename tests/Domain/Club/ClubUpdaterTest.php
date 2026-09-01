<?php

declare(strict_types=1);

namespace Tests\Domain\Club;

use App\Domain\Auth\Impression;
use App\Domain\Club\Club;
use App\Domain\Club\ClubInfo;
use App\Domain\Club\ClubInput;
use App\Domain\Club\ClubRepository;
use App\Domain\Club\ClubUpdater;
use App\Domain\Club\Exception\ClubAlreadyExist;
use App\Domain\Club\PreventDuplicateClubUpdater;
use App\Domain\Club\StandardClubUpdater;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClubUpdaterTest extends TestCase
{
    #[Test]
    public function standard_updater_passes_a_new_impression_to_the_aggregate(): void
    {
        $clock = $this->createMock(Clock::class);
        $clock->expects($this->once())->method('now')->willReturn(Carbon::parse('2026-01-01'));
        $club = $this->createMock(Club::class);
        $input = new ClubInput(new ClubInfo('Новы клуб', 'новы клуб'), 7);
        $club->expects($this->once())->method('updateInfo')->with(
            $input->info,
            $this->isInstanceOf(Impression::class),
        );

        $this->assertSame($club, new StandardClubUpdater($clock)->update($club, $input));
    }

    #[Test]
    public function duplicate_updater_rejects_another_club_before_delegating(): void
    {
        $decorated = $this->createMock(ClubUpdater::class);
        $clubs = $this->createMock(ClubRepository::class);
        $club = $this->clubStub(1);
        $clubs->expects($this->once())->method('oneByCriteria')->with(
            new Criteria(['normalizedName' => 'новы клуб', 'excludeId' => 1]),
        )->willReturn($this->createStub(Club::class));
        $decorated->expects($this->never())->method('update');

        $this->expectException(ClubAlreadyExist::class);
        new PreventDuplicateClubUpdater($decorated, $clubs)->update(
            $club,
            new ClubInput(new ClubInfo('Новы клуб', 'новы клуб'), 7),
        );
    }

    #[Test]
    public function duplicate_updater_delegates_when_only_the_current_club_matches(): void
    {
        $decorated = $this->createMock(ClubUpdater::class);
        $clubs = $this->createMock(ClubRepository::class);
        $club = $this->clubStub(1);
        $input = new ClubInput(new ClubInfo('Новы клуб', 'новы клуб'), 7);

        $clubs->expects($this->once())->method('oneByCriteria')->with(
            new Criteria(['normalizedName' => 'новы клуб', 'excludeId' => 1]),
        )->willReturn(null);
        $decorated->expects($this->once())->method('update')->with($club, $input)->willReturn($club);

        $this->assertSame($club, new PreventDuplicateClubUpdater($decorated, $clubs)->update($club, $input));
    }

    private function clubStub(int $id): Club
    {
        $club = $this->createStub(Club::class);
        $club->method('__get')->willReturnMap([['id', $id]]);

        return $club;
    }
}
