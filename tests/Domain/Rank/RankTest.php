<?php

declare(strict_types=1);

namespace Tests\Domain\Rank;

use App\Domain\Rank\Rank;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RankTest extends TestCase
{
    #[Test]
    public function it_exposes_stable_ids_and_labels_in_domain_order(): void
    {
        $this->assertSame(9, Rank::WorldClassMaster->value);
        $this->assertSame('МСМК', Rank::WorldClassMaster->label());
        $this->assertSame(0, Rank::WithoutRank->value);
        $this->assertSame('б/р', Rank::WithoutRank->label());
    }

    #[Test]
    public function it_knows_activation_and_stronger_rank_rules(): void
    {
        $this->assertFalse(Rank::MasterOfSport->isAutomaticallyActivated());
        $this->assertFalse(Rank::FirstRank->isAutomaticallyActivated());
        $this->assertTrue(Rank::SecondRank->isAutomaticallyActivated());
        $this->assertTrue(Rank::JuniorFirstRank->isAutomaticallyActivated());
        foreach ([Rank::SecondRank, Rank::ThirdRank, Rank::JuniorFirstRank, Rank::JuniorSecondRank, Rank::JuniorThirdRank] as $rank) {
            $this->assertTrue($rank->usesDelayedActivation());
        }
        foreach ([Rank::WithoutRank, Rank::FirstRank, Rank::CandidateMaster, Rank::MasterOfSport, Rank::WorldClassMaster] as $rank) {
            $this->assertFalse($rank->usesDelayedActivation());
        }
        $this->assertSame([Rank::SecondRank, Rank::FirstRank, Rank::CandidateMaster, Rank::MasterOfSport, Rank::WorldClassMaster], Rank::ThirdRank->strongerRanks());
    }
}
