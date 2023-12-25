<?php
declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\IdentProtocolLineCommand;
use App\Console\Commands\RankValidationCommand;
use App\Console\Commands\RecalculatingRanks;
use App\Console\Commands\SimpleIndentCommand;
use App\Console\Commands\StartBigIdentCommand;
use App\Console\Commands\SyncPersonsCommand;
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
        RecalculatingRanks::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(SimpleIndentCommand::class)->dailyAt('01:00')->runInBackground();
        $schedule->command(StartBigIdentCommand::class)->monthly()->at('03:00')->runInBackground();
        $schedule->command(RankValidationCommand::class)->weekly()->at('02:00')->runInBackground();
        //        $schedule->command(SyncPersonsCommand::class)->weekly()->runInBackground();

        for ($i = 0; $i < 4; $i++) {
            $schedule->command(IdentProtocolLineCommand::class)
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
