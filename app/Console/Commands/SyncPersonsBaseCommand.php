<?php

namespace App\Console\Commands;

use App\Services\GoogleService;
use Illuminate\Console\Command;

/**
 * Команда для синхронизации базы людей с файлом от федерации (гугл док).
 * Запускаем раз в день
 *
 * 1 2 * * * php artisan persons:sync
 *
 * Class SyncPersonsBaseCommand
 * @package App\Console\Commands
 */
class SyncPersonsBaseCommand extends Command
{
    protected $signature = 'persons:sync';

    public function handle(): void
    {
        $startTime = time();
        $this->info('Start');
        $service = (new GoogleService())->getSheetsService();
        $spreadsheetId = env('SYNC_FILE');
        $range = 'Class Data!A:E';
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if (empty($values)) {
            print "No data found.\n";
        } else {
            print "Name, Major:\n";
            foreach ($values as $row) {
                // Print columns A and E, which correspond to indices 0 and 4.
                printf("%s, %s\n", $row[0], $row[4]);
            }
        }

        $time = time() - $startTime;
        $this->info("Time for query: {$time}");
        $this->info("Finish");
    }
}
