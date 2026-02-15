<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\SportOrgParser;

final class SportOrgParserTest extends AbstractParser
{
    public static function dataProvider(): array
    {
        return [
            [
                '2026/17.01.26.html',
                130,
                [
                    123 => [
                        'Максименко',       // фамилия
                        'София',            // имя
                        'ГУДО "Родничок"',  // клуб
                        2013,               // год
                        'б/р',               // разряд
                        8,                  // номер
                        '00:13:12',          // время
                        1,                  // место
                        null,               // выполненный разряд
                        null,               // очки
                    ],
                    124 => [
                        'Клименкова',           // фамилия
                        'Алёна',               // имя
                        'г. Жодино', // клуб
                        2016,                 // год
                        'б/р',                 // разряд
                        7,                  // номер
                        '00:29:32',              // время
                        null,                 // место
                        null,                 // очки
                        null,                 // выполненный разряд
                        true
                    ],
                    119 => [
                        'Шинкевич',           // фамилия
                        'София',               // имя
                        'г. Жодино', // клуб
                        2013,                 // год
                        'IIю',                 // разряд
                        14,                  // номер
                        null,              // время
                        null,                 // место
                        null,                 // очки
                        null,                 // выполненный разряд
                    ],
                ],
            ],
        ];
    }
    protected function getAllGroups(): array
    {
        return [];
    }
    protected function getParser(): string
    {
        return SportOrgParser::class;
    }
}
