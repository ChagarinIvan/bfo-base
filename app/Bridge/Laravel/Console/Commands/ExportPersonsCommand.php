<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Console\Commands;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Rank\ActivePersonRank;
use App\Application\Service\Rank\ActivePersonRankService;
use App\Domain\Person\Person;
use App\Infrastracture\Integration\OrientBy\OrientByPersonDto;
use App\Infrastracture\Integration\OrientBy\OrientBySyncService;
use App\Services\PersonsService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Log\LogManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;
use function unserialize;

class ExportPersonsCommand extends Command
{
    protected $signature = 'persons:export';
    private LoggerInterface $logger;

    public function __construct(
        private readonly PersonsService $service,
        private readonly Filesystem $storage,
        private readonly ActivePersonRankService $rankService,
        LogManager $loggerManager,
    ) {
        parent::__construct();
        $this->logger = $loggerManager->channel('sync');
    }

    public function handle(): void
    {
        $this->logger->info('Start.');

        $filePath = 'exports/ranks.csv';

        // открываем поток на запись
        $stream = $this->storage->writeStream($filePath);

        if ($stream === false) {
            throw new \RuntimeException('Cannot open file for writing');
        }

        // заголовки
        fputcsv($stream, ['lastname', 'firstname', 'birthday', 'rank']);

        $persons = $this->service->getPersonsList();

        /** @var Person $person */
        foreach ($persons->cursor() as $person) {
            fputcsv($stream, [
                $person->lastname,
                $person->firstname,
                $person->birthday?->format('Y-m-d'),
                $this->rankService
                    ->execute(new ActivePersonRank((string) $person->id))
                    ?->rank,
            ]);
        }

        fclose($stream);

        $this->logger->info('Success.', ['file' => $filePath]);
    }

    protected function configure(): void
    {
        $this
            ->setName('persons:export')
            ->setDescription('Export all persons in file');
    }
}
