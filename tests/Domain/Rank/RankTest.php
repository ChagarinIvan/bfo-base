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
        $this->assertSame('world_class_master', Rank::WorldClassMaster->value);
        $this->assertSame('МСМК', Rank::WorldClassMaster->label());
        $this->assertSame('without_rank', Rank::WithoutRank->value);
        $this->assertSame('б/р', Rank::WithoutRank->label());
    }

    #[Test]
    public function it_normalizes_protocol_values_to_enum_cases(): void
    {
        $this->assertSame(Rank::CandidateMaster, Rank::fromProtocolValue(' KMC '));
        $this->assertSame(Rank::JuniorFirstRank, Rank::fromProtocolValue('1ю'));
        $this->assertSame(Rank::WithoutRank, Rank::fromProtocolValue('Б/Р'));
        $this->assertNotInstanceOf(Rank::class, Rank::fromProtocolValue('-'));
    }

    #[Test]
    public function it_knows_activation_and_stronger_rank_rules(): void
    {
        $this->assertFalse(Rank::MasterOfSport->isAutomaticallyActivated());
        $this->assertTrue(Rank::FirstRank->isAutomaticallyActivated());
        $this->assertSame([Rank::SecondRank, Rank::FirstRank, Rank::CandidateMaster, Rank::MasterOfSport, Rank::WorldClassMaster], Rank::ThirdRank->strongerRanks());
    }
}
