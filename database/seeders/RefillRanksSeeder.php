<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProtocolLine;
use App\Services\RankService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * php artisan db:seed --class=RefillRanksSeeder
 */
class RefillRanksSeeder extends Seeder
{
    private RankService $rankService;

    public function __construct(RankService $rankService)
    {
        $this->rankService = $rankService;
    }

    public function run(): void
    {
        $this->rankService->cleanAll();

        $protocolLines = ProtocolLine::with('distance.event')
            ->where('complete_rank', '<>', '')
            ->get();

        $protocolLinesGroupedByPersons = $protocolLines->groupBy('person_id');
        $protocolLinesGroupedByPersons = $protocolLinesGroupedByPersons->transform(static fn (Collection $protocolLines) => $protocolLines->sortBy('distance.event.date'));
        foreach ($protocolLinesGroupedByPersons as $protocolLines) {
            foreach ($protocolLines as $protocolLine) {
                /** @var ProtocolLine $protocolLine */
                $this->rankService->reFillRanksForPerson($protocolLine->person_id);
            }
        }
    }
}
