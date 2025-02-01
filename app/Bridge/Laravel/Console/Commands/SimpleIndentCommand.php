<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Services\ProtocolLineIdentService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use function time;

/**
 * Команда для определения людей с помощью прямого совпадения идентификатора.
 * Запускаем раз в день
 */
class SimpleIndentCommand extends Command
{
    protected $signature = 'protocol-lines:simple-ident';

    public function handle(ProtocolLineIdentService $identService): void
    {
        $this->info('Start');
        $userId = (int) $this->argument('user_id');

        $startTime = time();
        $protocolLines = ProtocolLine::whereNull('person_id')->get();
        $this->info("Has {$protocolLines->count()} lines");
        $notIndentCount = $identService->simpleIdent($protocolLines)->count();
        $this->info('Affected rows count is ' . ($protocolLines->count() - $notIndentCount));
        $time = time() - $startTime;
        $this->info("Time for query: $time");
        $this->info("Finish");
    }

    protected function configure(): void
    {
        $this
            ->setName('protocol-lines:simple-ident')
            ->setDescription('Simple ident protocol line.')
            ->addArgument(
                'user_id',
                InputArgument::REQUIRED,
                'User Id for impression,'
            )
        ;
    }
}
