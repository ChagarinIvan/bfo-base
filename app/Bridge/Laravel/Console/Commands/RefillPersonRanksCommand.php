<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Service\Rank\RebuildPersonRanks;
use App\Application\Service\Rank\RebuildPersonRanksService;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ranks:refill')]
#[Description('Rebuilds materialized person ranks from protocol lines')]
final class RefillPersonRanksCommand extends Command
{
    public function handle(PersonRepository $persons, RebuildPersonRanksService $rebuild): int
    {
        foreach ($persons->byCriteria(Criteria::empty()) as $person) {
            $rebuild->execute(new RebuildPersonRanks($person->id));
        }

        return self::SUCCESS;
    }
}
