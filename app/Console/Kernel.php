<?php

namespace App\Console;

use App\Console\Commands\IdentProtocolLineCommand;
use App\Console\Commands\RankValidationCommand;
use App\Console\Commands\RecalculatingRanks;
use App\Console\Commands\SimpleIndentCommand;
use App\Console\Commands\StartBigIdentCommand;
use App\Console\Commands\SyncPersonsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        IdentProtocolLineCommand::class,
        SimpleIndentCommand::class,
        StartBigIdentCommand::class,
        RankValidationCommand::class,
        SyncPersonsCommand::class,
        RecalculatingRanks::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(SimpleIndentCommand::class)->dailyAt('01:00')->runInBackground();
        $schedule->command(StartBigIdentCommand::class)->monthly()->at('03:00')->runInBackground();
        $schedule->command(RankValidationCommand::class)->weekly()->at('02:00')->runInBackground();
//        $schedule->command(SyncPersonsCommand::class)->weekly()->runInBackground();

        for ($i = 0; $i < 4; $i++) {
            $schedule->command(IdentProtocolLineCommand::class)
                ->everyMinute()
                ->before(function() use ($i) {sleep($i * 15);})
                ->runInBackground();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
