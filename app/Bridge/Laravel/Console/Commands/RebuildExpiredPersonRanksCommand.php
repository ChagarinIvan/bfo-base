<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildExpiredPersonRanks;
use App\Application\Service\Person\RebuildExpiredPersonRanksService;
use App\Domain\Shared\Clock;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('persons:ranks:rebuild-expired {userId}')]
#[Description('Rebuilds ranks expired after the date boundary')]
final class RebuildExpiredPersonRanksCommand extends Command
{
    public function handle(RebuildExpiredPersonRanksService $service, Clock $clock): int
    {
        $count = $service->execute(new RebuildExpiredPersonRanks(
            userId: new UserId((int) $this->argument('userId')),
            criteria: ['rankFinishedBefore' => $clock->now()->copy()->startOfDay()],
        ));

        $this->info("Finished expired person-rank rebuild: {$count} people rebuilt.");

        return self::SUCCESS;
    }
}
