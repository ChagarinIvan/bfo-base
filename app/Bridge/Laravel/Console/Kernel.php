<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console;

use App\Bridge\Laravel\Console\Commands\DeleteInactivePersonsPromptsCommand;
use App\Bridge\Laravel\Console\Commands\FixInactivePersonsProtocolLinesCommand;
use App\Bridge\Laravel\Console\Commands\FixYearCommand;
use App\Bridge\Laravel\Console\Commands\IdentProtocolLineCommand;
use App\Bridge\Laravel\Console\Commands\PruneInactivePersonsCommand;
use App\Bridge\Laravel\Console\Commands\RebuildExpiredPersonRanksCommand;
use App\Bridge\Laravel\Console\Commands\RefillPersonRanksCommand;
use App\Bridge\Laravel\Console\Commands\SimpleIndentCommand;
use App\Bridge\Laravel\Console\Commands\StartBigIdentCommand;
use App\Bridge\Laravel\Console\Commands\SyncPersonsCommand;
use App\Bridge\Laravel\Console\Commands\SyncStoredPersonsCommand;
use App\Domain\Auth\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Override;
use function sleep;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    #[Override]
    protected $commands = [
        IdentProtocolLineCommand::class,
        SimpleIndentCommand::class,
        StartBigIdentCommand::class,
        SyncPersonsCommand::class,
        SyncStoredPersonsCommand::class,
        FixYearCommand::class,
        RefillPersonRanksCommand::class,
        RebuildExpiredPersonRanksCommand::class,
        PruneInactivePersonsCommand::class,
        DeleteInactivePersonsPromptsCommand::class,
        FixInactivePersonsProtocolLinesCommand::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(SimpleIndentCommand::class, ['userId' => User::SYSTEM_USER_ID])->dailyAt('01:00')->runInBackground();
        $schedule->command(PruneInactivePersonsCommand::class, ['userId' => User::SYSTEM_USER_ID])->dailyAt('02:00')->runInBackground();
        $schedule->command(StartBigIdentCommand::class, ['userId' => User::SYSTEM_USER_ID])->dailyAt('03:00')->runInBackground();
        $schedule->command(RebuildExpiredPersonRanksCommand::class, ['userId' => User::SYSTEM_USER_ID])->dailyAt('00:10')->runInBackground();
        //        $schedule->command(SyncPersonsCommand::class)->weekly()->runInBackground();

        for ($i = 0; $i < 4; $i++) {
            $schedule->command(IdentProtocolLineCommand::class, ['userId' => User::SYSTEM_USER_ID])
                ->everyMinute()
                ->before(static function () use ($i): void {sleep($i * 15);})
                ->runInBackground();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
