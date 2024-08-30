<?php

declare(strict_types=1);

namespace Services;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Services\PersonPromptService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use Carbon\Carbon;
use Database\Seeders\RankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Mav\Slovo\Phonetics;
use Tests\CreatesApplication;

class RankServiceTest extends \Tests\TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    /** @test */
    public function it_refills_person_ranks(): void
    {
        /** @var RankService $rankService */
        $rankService = $this->app->get(RankService::class);
        $this->seed(RankSeeder::class);

        $rankService->reFillRanksForPerson(1001);
        $ranks = $rankService->getPersonRanks(1001);
        $this->assertCount(7, $ranks);

        $actualRank = $rankService->getActiveRank(1001);
        $this->assertEquals(Rank::FIRST_RANK, $actualRank->rank);

        $ranks = $rankService->getPersonRanks(1001);
        $this->assertCount(7, $ranks);

        $rankService->activateRank($ranks->first(), Carbon::createFromFormat('Y-m-d', '2024-07-01'));

        $actualRank = $rankService->getActiveRank(1001);
        $this->assertEquals(Rank::SMC_RANK, $actualRank->rank);
    }
}
