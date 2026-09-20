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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use function array_key_exists;
use function in_array;
use function levenshtein;
use function sprintf;
use function str_replace;

class ProtocolLineIdentService
{
    /**
     * карта исправления символов, например случайно поставленные английские символы совпадающие по написанию с русскими
     */
    public const SYMBOL_MAP = [
        'с' => ['c'],
        'а' => ['a'],
        'о' => ['o'],
        'у' => ['y'],
        'р' => ['p'],
        'х' => ['x'],
        'е' => ['e', 'ё'],
    ];

    /**
     * карта исправления имён, разные сокращения и формы аналоги
     */
    private const array EDIT_MAP = [
        'дмитрий' => ['дима'],
        'павел' => ['паша'],
        'мария' => ['маша'],
        'иван' => ['ваня'],
        'татьяна' => ['таня'],
        'анастасия' => ['настя'],
        'екатерина' => ['катя'],
        'юрий' => ['юра'],
        'ольга' => ['оля'],
        'валентина' => ['валя'],
        'александр' => ['саша'],
        'алексей' => ['леша'],
        'светлана' => ['света'],
        'владислав' => ['влад'],
        'вячеслав' => ['слава'],
        'наталья' => ['наташа'],
        'михаил' => ['миша'],
        'анна' => ['аня'],
        'елена' => ['лена'],
    ];

    private static ?array $normalizedNamesMap = null;

    private static Collection $prompts;

    /**
     * Процесс нормализации фамилии имени (везде идёт замена неверных символов, заменяются формы имени)
     *
     * TODO: удалить static API и использовать NameNormalizer в потребителях.
     */
    public static function prepareLine(string $line): string
    {
        // Исправляем символы
        foreach (self::SYMBOL_MAP as $symbol => $analogs) {
            $line = str_replace($analogs, $symbol, $line);
        }

        // Нормализация имени
        $map = self::getNormalizedNamesMap();

        return $map[$line] ?? $line;
    }

    private static function getNormalizedNamesMap(): array
    {
        if (self::$normalizedNamesMap !== null) {
            return self::$normalizedNamesMap;
        }

        $map = [];

        foreach (self::EDIT_MAP as $name => $analogs) {
            foreach ($analogs as $analog) {
                $map[$analog] = $name;
            }
        }

        return self::$normalizedNamesMap = $map;
    }

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
        $lines = ProtocolLine::query()
            ->with('distance.event')
            ->whereKey($protocolLines->pluck('id'))
            ->get()
        ;

        $hasPreviousActivation = [];
        $activatedLineIds = [];

        foreach ($lines as $line) {
            $rank = Rank::fromProtocolValue($line->complete_rank);
            if (
                $line->person_id === null
                || $line->activate_rank !== null
                || !in_array($rank, [Rank::CandidateMaster, Rank::MasterOfSport], true)
            ) {
                continue;
            }

            $eventDate = $line->distance->event->date;
            $key = $line->person_id . ':' . $rank->value . ':' . $eventDate->format('Y-m-d');
            if (!array_key_exists($key, $hasPreviousActivation)) {
                $hasPreviousActivation[$key] = ProtocolLine::query()
                    ->where('person_id', $line->person_id)
                    ->where('complete_rank', $rank->label())
                    ->whereNotNull('activate_rank')
                    ->where('id', '!=', $line->id)
                    ->whereHas('distance.event', static function (Builder $query) use ($eventDate): void {
                        $query->where('date', '<', $eventDate);
                    })
                    ->exists()
                ;
            }

            if (!$hasPreviousActivation[$key]) {
                continue;
            }

            $activatedLineIds[] = $line->id;
            if (!$dryRun) {
                $line->activate_rank = $eventDate;
                $line->save();
            }
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
