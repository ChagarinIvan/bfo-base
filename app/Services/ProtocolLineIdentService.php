<?php

declare(strict_types=1);

namespace App\Services;

use App\Bridge\Laravel\Jobs\RebuildPersonRanksJob;
use App\Domain\Auth\Impression;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\PersonPrompt\TranslitPersonPromptMetaphone;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineOperations;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use App\Models\IdentLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use function levenshtein;
use function sprintf;
use function str_replace;

class ProtocolLineIdentService
{
    private static Collection $prompts;

    public function __construct(
        private readonly ProtocolLineOperations $protocolLineService,
        private readonly PersonPromptRepository $personPrompts,
        private readonly TranslitPersonPromptMetaphone $metaphone,
    ) {
    }

    /**
     * Запускаем процесс идентификации людей в строчках протокола
     * состоит из 2 частей:
     * - по прямому совпадению идентификатора (на лету)
     * - по расстоянию левенштейна (в очередь)
     */
    public function identPersons(Collection $protocolLines, Impression $impression): void
    {
        // пробуем идентифицировать людей из нового протокола прямым подобием идентификационных строк
        $notIdentedLines = $this->simpleIdent($protocolLines);
        Log::info(sprintf('Not idented %d lines.', $notIdentedLines->count()));
        $protocolLines = $protocolLines->keyBy('id');
        $notIdentedLines = $notIdentedLines->keyBy('id');
        $identedLines = ProtocolLine::find($protocolLines->diffKeys($notIdentedLines)->keys());
        Log::info(sprintf('Idented %d lines.', $identedLines->count()));
        $this->activateRepeatedMasterRanks($identedLines);

        // Пересчитываем затронутых спортсменов одной идемпотентной batch-задачей.
        $personIds = $identedLines->pluck('person_id')->filter()->unique()->values()->all();
        if ($personIds !== []) {
            RebuildPersonRanksJob::dispatch($personIds, $impression);
        }

        // create ident line
        $this->pushIdentLines($notIdentedLines->pluck('prepared_line')->unique());
    }

    /**
     * Идентификация прямым запросом в базу на поиск линий протокола,
     * с такой же "идентификационной" строкой и имеющимся person_id.
     *
     * На вход коллекция линий протокола, на выходе строки протокола, у которых не определилсь люди.
     * Используется при создании или редактировании протокола соревнований для быстрой идентификации части людей.
     *
     * @param Collection|ProtocolLine[] $protocolLines
     */
    public function simpleIdent(Collection $protocolLines): Collection
    {
        $linesIds = $protocolLines->pluck('id');
        $this->protocolLineService->fastIdent($linesIds->all());

        return new Collection($this->protocolLineService->getProtocolLinesInListWithoutPerson($linesIds->all()));
    }

    /**
     * @param Collection<int, ProtocolLine> $protocolLines
     * @return list<int>
     */
    public function activateRepeatedMasterRanks(Collection $protocolLines, bool $dryRun = false): array
    {
        $lines = $this->repeatedMasterRankLines($protocolLines)->get();
        $activatedLineIds = $lines->pluck('id')->all();

        if ($dryRun) {
            return $activatedLineIds;
        }

        foreach ($lines as $line) {
            $line->setAttribute('activate_rank', $line->getAttribute('activation_event_date'));
            $line->save();
        }

        return $activatedLineIds;
    }

    /**
     * Определяет людей вначале делает короткий список с почти одинаковым звучанием,
     * а потом уже по идентификатору с использованием расстояния левенштайна.
     */
    public function identPerson(string $searchLine): int
    {
        Log::info(sprintf('Ident person %s.', $searchLine));

        self::$prompts ??= $this->personPrompts->byCriteria(Criteria::empty());

        $metaphone = $this->metaphone->calculate($searchLine);
        $ranks = new Collection();

        foreach (self::$prompts->pluck('metaphone') as $prompt) {
            $rank = levenshtein($metaphone, $prompt);
            $ranks->push([
                'metaphone' => $prompt,
                'rank' => $rank,
            ]);
        }

        /** @var array<string, string|int> $minRank */
        $minRank = $ranks->sortBy('rank')->first();
        if ($minRank['rank'] <= 2) {
            $prompts = self::$prompts->where('metaphone', $minRank['metaphone']);

            return $this->identByPersonPrompt($searchLine, $prompts);
        }

        return 0;
    }

    /**
     * @param Collection|string[] $protocolLines
     */
    public function pushIdentLines(Collection $protocolLines): void
    {
        Log::info(sprintf('pushIdentLines %d.', $protocolLines->count()));

        foreach ($protocolLines as $line) {
            $identLinesCount = IdentLine::whereIdentLine($line)->count();
            Log::info(sprintf('Line added %s.', $line));

            if ($identLinesCount === 0) {
                $ident = new IdentLine();
                $ident->ident_line = $line;
                $ident->save();
            }
        }
    }

    /** @return Builder<ProtocolLine> */
    private function repeatedMasterRankLines(Collection $protocolLines): Builder
    {
        return ProtocolLine::query()
            ->select('protocol_lines.*')
            ->addSelect('current_events.date as activation_event_date')
            ->join('distances as current_distances', 'current_distances.id', '=', 'protocol_lines.distance_id')
            ->join('events as current_events', 'current_events.id', '=', 'current_distances.event_id')
            ->whereKey($protocolLines->pluck('id')->all())
            ->whereNotNull('protocol_lines.person_id')
            ->whereNull('protocol_lines.activate_rank')
            ->whereIn('protocol_lines.complete_rank', [Rank::CandidateMaster->label(), Rank::MasterOfSport->label()])
            ->whereExists(static function (QueryBuilder $query): void {
                $query
                    ->selectRaw('1')
                    ->from('protocol_lines as previous_lines')
                    ->join('distances as previous_distances', 'previous_distances.id', '=', 'previous_lines.distance_id')
                    ->join('events as previous_events', 'previous_events.id', '=', 'previous_distances.event_id')
                    ->whereColumn('previous_lines.person_id', 'protocol_lines.person_id')
                    ->whereColumn('previous_lines.complete_rank', 'protocol_lines.complete_rank')
                    ->whereNotNull('previous_lines.activate_rank')
                    ->whereColumn('previous_lines.id', '!=', 'protocol_lines.id')
                    ->whereColumn('previous_events.date', '<', 'current_events.date')
                ;
            })
        ;
    }

    /**
     * Определяем людей по идентификаторам с использованием расстояния левенштайна.
     */
    private function identByPersonPrompt(string $searchLine, Collection $prompts): int
    {
        $ranks = new Collection();

        foreach ($prompts as $prompt) {
            $rank = levenshtein($searchLine, $prompt->prompt);
            $ranks->push([
                'prompt' => $prompt->prompt,
                'rank' => $rank,
            ]);
        }

        /** @var Collection $minRank */
        $minRank = $ranks->sortBy('rank')->first();
        if ($minRank['rank'] <= 5) {
            $prompt = $prompts->where('prompt', $minRank['prompt'])->first();

            return $prompt->person_id;
        }

        return 0;
    }
}
