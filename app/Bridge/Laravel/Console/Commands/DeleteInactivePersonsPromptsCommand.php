<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\PersonPrompt\DeletePersonPrompt;
use App\Application\Service\PersonPrompt\DeletePersonPromptService;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;
use function count;

/**
 * Команда для удаления промптов у неактивных персон.
 */
#[Description('Delete all prompts for inactive persons')]
#[Signature('prompts:delete-inactive')]
class DeleteInactivePersonsPromptsCommand extends Command
{
    public function __construct(
        private readonly PersonPromptRepository $prompts,
        private readonly DeletePersonPromptService $deletePromptService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting deletion of prompts for inactive persons...');

        // Получаем все промпты неактивных персон
        $prompts = $this->prompts->byCriteria(new Criteria(['activePerson' => false]));

        $totalCount = count($prompts);

        if ($totalCount === 0) {
            $this->info('No prompts found for inactive persons.');
            return self::SUCCESS;
        }

        $this->info("Found {$totalCount} prompts for inactive persons.");

        $progressBar = $this->output->createProgressBar($totalCount);
        $progressBar->start();

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($prompts as $prompt) {
            try {
                $this->deletePromptService->execute(new DeletePersonPrompt((string) $prompt->id, new UserId(0)));
                $deletedCount++;
            } catch (Throwable $e) {
                $failedCount++;
                $this->error("\nFailed to delete prompt {$prompt->id}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Deletion completed:");
        $this->info("  - Successfully deleted: {$deletedCount}");

        if ($failedCount > 0) {
            $this->warn("  - Failed: {$failedCount}");
        }

        return self::SUCCESS;
    }
}
