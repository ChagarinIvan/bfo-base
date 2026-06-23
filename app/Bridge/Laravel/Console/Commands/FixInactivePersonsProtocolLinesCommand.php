<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Команда для очистки связи protocol_line с неактивными персонами.
 * Устанавливает person_id = null для всех protocol_line, которые ссылаются на неактивных персон.
 */
class FixInactivePersonsProtocolLinesCommand extends Command
{
    protected $signature = 'protocol-lines:fix-inactive-persons';

    protected $description = 'Set person_id to null for protocol lines linked to inactive persons';

    public function handle(): int
    {
        $this->info('Starting to fix protocol lines for inactive persons...');

        // Находим все protocol_line которые ссылаются на неактивных персон
        $affectedLines = DB::table('protocol_lines')
            ->join('person', 'person.id', '=', 'protocol_lines.person_id')
            ->where('person.active', false)
            ->whereNotNull('protocol_lines.person_id')
            ->select('protocol_lines.id')
            ->get();

        $totalCount = $affectedLines->count();

        if ($totalCount === 0) {
            $this->info('No protocol lines found for inactive persons.');
            return self::SUCCESS;
        }

        $this->info("Found {$totalCount} protocol lines linked to inactive persons.");

        if (!$this->confirm('Do you want to proceed with setting person_id to null?', true)) {
            $this->warn('Operation cancelled.');
            return self::FAILURE;
        }

        $progressBar = $this->output->createProgressBar($totalCount);
        $progressBar->start();

        $updatedCount = 0;
        $failedCount = 0;

        foreach ($affectedLines as $line) {
            try {
                DB::table('protocol_lines')
                    ->where('id', $line->id)
                    ->update(['person_id' => null]);
                $updatedCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                $this->error("\nFailed to update protocol line {$line->id}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Update completed:");
        $this->info("  - Successfully updated: {$updatedCount}");

        if ($failedCount > 0) {
            $this->warn("  - Failed: {$failedCount}");
        }

        return self::SUCCESS;
    }
}
