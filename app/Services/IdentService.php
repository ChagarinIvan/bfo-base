<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IdentLine;
use App\Models\PersonPrompt;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class IdentService
 *
 * @package App\Services
 */
class IdentService
{
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
        DB::table('protocol_lines', 'pls')
            ->join('protocol_lines AS plj', 'plj.prepared_line', '=', 'pls.prepared_line')
            ->whereNull('pls.person_id')
            ->whereNotNull('plj.person_id')
            ->whereIn('pls.id', $linesIds)
            ->update(['pls.person_id' => DB::raw('plj.person_id')]);

        return ProtocolLine::whereIn('id', $linesIds)
            ->whereNull('person_id')
            ->get();
    }

    /**
     * Определяем людей по идентификаторам с использованием расстояния левенштайна.
     *
     * @param string $searchLine
     * @return int
     */
    public function identPerson(string $searchLine): int
    {
        $prompts = PersonPrompt::all();
        $ranks = collect();

        foreach ($prompts->pluck('prompt') as $prompt) {
            $rank = levenshtein($searchLine, $prompt);
            $ranks->push([
                'prompt' => $prompt,
                'rank' => $rank,
            ]);
        }

        $minRank = $ranks->sortBy('rank')->first();
        if ($minRank['rank'] <= 5) {
            /** @var PersonPrompt $prompt */
            $prompt = $prompts->where('prompt', $minRank['prompt'])->first();
            return $prompt->person_id;
        }
        return 0;
    }

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

    /**
     * @param Collection|string[] $protocolLines
     */
    public function pushIdentLines(Collection $protocolLines): void
    {
        foreach ($protocolLines as $line) {
            $identLinesCount = IdentLine::whereIdentLine($line)->count();

            if ($identLinesCount === 0) {
                $ident = new IdentLine();
                $ident->ident_line = $line;
                $ident->save();
            }
        }
    }
}
