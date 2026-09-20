<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Services\ProtocolLineIdentService;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use function array_unique;
use function array_values;
use function count;

#[Signature('protocol-lines:backfill-repeat-master-rank-activation {userId} {--dry-run}')]
final class BackfillRepeatMasterRankActivationCommand extends Command
{
    public function handle(
        ProtocolLineIdentService $identification,
        RebuildPersonRanksService $ranks,
    ): int {
        $dryRun = (bool) $this->option('dry-run');
        $userId = new UserId((int) $this->argument('userId'));
        $activatedLineCount = 0;
        $personIds = [];

        ProtocolLine::query()
            ->whereNotNull('person_id')
            ->whereNull('activate_rank')
            ->whereIn('complete_rank', [Rank::CandidateMaster->label(), Rank::MasterOfSport->label()])
            ->orderBy('id')
            ->chunkById(100, static function (Collection $lines) use ($identification, $dryRun, &$activatedLineCount, &$personIds): void {
                $activatedLineIds = $identification->activateRepeatedMasterRanks($lines, $dryRun);
                $activatedLineCount += count($activatedLineIds);
                $personIds = [...$personIds, ...$lines->whereIn('id', $activatedLineIds)->pluck('person_id')->all()];
            })
        ;

        $personIds = array_values(array_unique($personIds));
        if (!$dryRun) {
            foreach ($personIds as $personId) {
                try {
                    $ranks->execute(new RebuildPersonRanks($personId, $userId));
                } catch (PersonNotFound) {
                    // A person can be removed while this maintenance command is running.
                }
            }
        }

        $this->info(($dryRun ? 'Would activate ' : 'Activated ') . $activatedLineCount . ' protocol lines for ' . count($personIds) . ' people.');

        return self::SUCCESS;
    }
}
