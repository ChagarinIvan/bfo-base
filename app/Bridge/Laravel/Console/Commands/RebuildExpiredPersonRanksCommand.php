<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Service\Rank\RebuildExpiredPersonRanksService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ranks:rebuild-expired')]
#[Description('Rebuilds ranks expired after the date boundary')]
final class RebuildExpiredPersonRanksCommand extends Command
{
    public function handle(RebuildExpiredPersonRanksService $service): int
    {
        $service->execute();

        return self::SUCCESS;
    }
}
