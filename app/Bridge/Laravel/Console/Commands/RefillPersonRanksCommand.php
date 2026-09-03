<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildExpiredPersonRanks;
use App\Application\Service\Person\RebuildExpiredPersonRanksService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('persons:ranks:refill {userId}')]
#[Description('Rebuilds materialized person ranks from protocol lines')]
final class RefillPersonRanksCommand extends Command
{
    public function handle(RebuildExpiredPersonRanksService $service): int
    {
        $this->info('Starting person-rank refill.');

        $userId = new UserId((int) $this->argument('userId'));
        $count = $service->execute(new RebuildExpiredPersonRanks($userId));

        $this->info("Finished person-rank refill: {$count} people rebuilt.");

        return self::SUCCESS;
    }
}
