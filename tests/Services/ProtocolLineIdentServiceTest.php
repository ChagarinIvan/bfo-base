<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Services\PersonPromptService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use Database\Seeders\RankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Mav\Slovo\Phonetics;
use Tests\CreatesApplication;

class ProtocolLineIdentServiceTest extends \Tests\TestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createApplication();
        RefreshDatabaseState::$migrated = false;
    }

    public static function prepareLineDataProvider(): array
    {
        return [
            ['алена', 'алена'],
            ['елена', 'елена'],
            ['лена', 'елена'],
            ['алёна', 'алена'],
        ];
    }

    /**
     * @dataProvider prepareLineDataProvider
     * @test
     */
    public function prepare_line(string $name, string $expectedName): void
    {
        $this->assertEquals($expectedName, ProtocolLineIdentService::prepareLine($name));
    }

    /** @test */
    public function itIdentsPersons(): void
    {
        /** @var RankService $rankService */
        $rankService = $this->app->get(RankService::class);
        $this->seed(RankSeeder::class);

        $service = new ProtocolLineIdentService(
            $rankService,
            $linesService = $this->createMock(ProtocolLineService::class),
            $this->createMock(PersonPromptService::class),
            $this->createMock(Phonetics::class),
        );

        $line = ProtocolLine::factory(state: ['id' => 1001, 'distance_id' => 1001, 'complete_rank' => Rank::SMC_RANK, 'activate_rank' => null, 'person_id' => 1001])->createOne();
        $protocolLines = collect([$line]);

        $linesService->expects($this->once())->method('fastIdent');
        $linesService->expects($this->once())->method('getProtocolLinesInListWithoutPerson')->willReturn(collect());

        $service->identPersons($protocolLines);
        $rank = $rankService->getActiveRank($line->person_id);
    }
}
