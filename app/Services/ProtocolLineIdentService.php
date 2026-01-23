<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Models\IdentLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Mav\Slovo\Phonetics;
use function in_array;
use function levenshtein;
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
    private const EDIT_MAP = [
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

    private static Collection $prompts;

    /**
     * Процесс нормализации фамилии имени (везде идёт замена неверных символов, заменяются формы имени)
     *
     * @param string $line
     * @return string
     */
    public static function prepareLine(string $line): string
    {
        //Исправляем символы
        foreach (self::SYMBOL_MAP as $symbol => $analogs) {
            $line = str_replace($analogs, $symbol, $line);
        }

        //Заменяем формы имён
        foreach (self::EDIT_MAP as $name => $analogs) {
            if (in_array($line, $analogs, true)) {
                foreach ($analogs as $analog) {
                    if ($line === $analog) {
                        return $name;
                    }
                }
            }
        }

        return $line;
    }

    public function __construct(
        private readonly RankService $rankService,
        private readonly ProtocolLineService $protocolLineService,
        private readonly PersonPromptService $personPromptService,
        private readonly Phonetics $phonetics,
    ) {
    }

    /**
     * Запускаем процесс идентификации людей в строчках протокола
     * состоит из 2 частей:
     * - по прямому совпадению идентификатора (на лету)
     * - по расстоянию левенштейна (в очередь)
     *
     * @param Collection $protocolLines
     */
    public function identPersons(Collection $protocolLines): void
    {
        // пробуем идентифицировать людей из нового протокола прямым подобием идентификационных строк
        $notIdentedLines = $this->simpleIdent($protocolLines);
        Log::info(sprintf('Not idented %d lines.', $notIdentedLines->count()));
        $protocolLines = $protocolLines->keyBy('id');
        $notIdentedLines = $notIdentedLines->keyBy('id');
        $identedLines = ProtocolLine::find($protocolLines->diffKeys($notIdentedLines)->keys());
        Log::info(sprintf('Idented %d lines.', $identedLines->count()));
        // надо для определившихся добавить разряды
        foreach ($identedLines as $line) {
            Log::info(sprintf('Re fill person "%d" rank.', $line->person_id));
            /** @var ProtocolLine $line */
            $this->rankService->reFillRanksForPerson($line->person_id);
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
     * @return Collection|ProtocolLine[]
     */
    public function simpleIdent(Collection $protocolLines): Collection
    {
        $linesIds = $protocolLines->pluck('id');
        $this->protocolLineService->fastIdent($linesIds);

        return $this->protocolLineService->getProtocolLinesInListWithoutPerson($linesIds);
    }

    /**
     * Определяет людей вначале делает короткий список с почти одинаковым звучанием,
     * а потом уже по идентификатору с использованием расстояния левенштайна.
     */
    public function identPerson(string $searchLine): int
    {
        Log::info(sprintf('Ident person %s.', $searchLine));

        self::$prompts = self::$prompts ?? $this->personPromptService->all();

        $metaphone = $this->phonetics->metaphour($searchLine);
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
            /** @var PersonPrompt $prompt */
            $prompt = $prompts->where('prompt', $minRank['prompt'])->first();

            return $prompt->person_id;
        }

        return 0;
    }
}
