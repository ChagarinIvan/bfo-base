<?php

namespace App\Console;

use App\Console\Commands\IdentProtocolLineCommand;
use App\Console\Commands\RankValidationCommand;
use App\Console\Commands\SimpleIndentCommand;
use App\Console\Commands\StartBigIdentCommand;
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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//        $schedule->command('protocol-lines:queue-ident')->everyMinute();
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
