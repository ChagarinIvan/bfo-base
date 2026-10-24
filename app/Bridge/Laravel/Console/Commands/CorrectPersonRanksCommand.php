<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\CorrectPersonRanks;
use App\Application\Service\Person\CorrectPersonRanksService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use function ctype_digit;
use function json_encode;

#[Signature('persons:ranks:correct {userId} {--personId=} {--apply}')]
#[Description('Preview rank corrections; --apply explicitly saves the changes')]
final class CorrectPersonRanksCommand extends Command
{
    public function handle(CorrectPersonRanksService $service): int
    {
        $userId = (string) $this->argument('userId');
        $personId = $this->option('personId');
        if (!ctype_digit($userId) || (int) $userId < 1 || ($personId !== null && (!ctype_digit((string) $personId) || (int) $personId < 1))) {
            $this->error('userId and personId must be positive integers.');

            return self::INVALID;
        }
        $count = 0;
        foreach ($service->execute(new CorrectPersonRanks(new UserId((int) $userId), $personId === null ? null : (int) $personId, (bool) $this->option('apply'))) as $result) {
            $this->line(json_encode($result, JSON_THROW_ON_ERROR));
            $count++;
        }
        $this->info(($this->option('apply') ? 'Applied: ' : 'Preview: ') . $count);

        return self::SUCCESS;
    }
}
