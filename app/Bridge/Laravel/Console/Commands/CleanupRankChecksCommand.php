<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Domain\RankCheck\RankCheckRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Storage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Description('Remove completed rank checks older than 24 hours')]
#[Signature('rank-checks:cleanup')]
final class CleanupRankChecksCommand extends Command
{
    public function handle(Storage $storage, RankCheckRepository $checks): int
    {
        $expired = $checks->byCriteria(new Criteria([
            'createdBefore' => Carbon::now()->subDay(),
        ]));

        foreach ($expired as $check) {
            $storage->delete($check->source_path);
            $checks->delete($check);
        }

        return self::SUCCESS;
    }
}
