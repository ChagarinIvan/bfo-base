<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console;

use App\Bridge\Laravel\Console\Commands\FixRankCommand;
use App\Bridge\Laravel\Console\Commands\FixYearCommand;
use App\Bridge\Laravel\Console\Commands\IdentProtocolLineCommand;
use App\Bridge\Laravel\Console\Commands\PruneInactivePersonsCommand;
use App\Bridge\Laravel\Console\Commands\RankValidationCommand;
use App\Bridge\Laravel\Console\Commands\RecalculatingRanks;
use App\Bridge\Laravel\Console\Commands\ReFillPersonRanksCommand;
use App\Bridge\Laravel\Console\Commands\SimpleIndentCommand;
use App\Bridge\Laravel\Console\Commands\StartBigIdentCommand;
use App\Bridge\Laravel\Console\Commands\SyncPersonsCommand;
use App\Bridge\Laravel\Console\Commands\SyncStoredPersonsCommand;
use App\Domain\User\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use function sleep;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     */
    protected $commands = [
        IdentProtocolLineCommand::class,
        SimpleIndentCommand::class,
        StartBigIdentCommand::class,
        RankValidationCommand::class,
        SyncPersonsCommand::class,
        SyncStoredPersonsCommand::class,
        RecalculatingRanks::class,
        FixRankCommand::class,
        FixYearCommand::class,
        ReFillPersonRanksCommand::class,
        PruneInactivePersonsCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(SimpleIndentCommand::class, ['userId' => User::SYSTEM_USER_ID])->dailyAt('01:00')->runInBackground();
        $schedule->command(PruneInactivePersonsCommand::class, ['userId' => User::SYSTEM_USER_ID])->dailyAt('02:00')->runInBackground();
        $schedule->command(StartBigIdentCommand::class, ['userId' => User::SYSTEM_USER_ID])->monthly()->at('03:00')->runInBackground();
        $schedule->command(RankValidationCommand::class, ['userId' => User::SYSTEM_USER_ID])->weekly()->at('02:00')->runInBackground();
        //        $schedule->command(SyncPersonsCommand::class)->weekly()->runInBackground();

        for ($i = 0; $i < 4; $i++) {
            $schedule->command(IdentProtocolLineCommand::class, ['user_id' => User::SYSTEM_USER_ID])
                ->everyMinute()
                ->before(static function () use ($i): void {sleep($i * 15);})
                ->runInBackground();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
