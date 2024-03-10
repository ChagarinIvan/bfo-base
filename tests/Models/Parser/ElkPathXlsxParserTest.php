<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\ElkPathXlsxParser;

final class ElkPathXlsxParserTest extends AbstractParser
{
    public static function dataProvider(): array
    {
        return [
            [
                '2024/03022024.xlsx',
                362,
                [
                    0 => [
                        'Мартысевич',  // фамилия
                        'Светлана',    // имя
                        'Готика',      // клуб
                        1995,          // год
                        null,          // разряд
                        4,             // номер
                        '2:05:46',     // время
                        1,             // место
                        null,          // выполненный разряд
                        null,          // очки
                    ],
                    321 => [
                        'Синкевич',           // фамилия
                        'Анна',               // имя
                        'RUN4FUN.BY Vitebsk', // клуб
                        2011,                 // год
                        null,                 // разряд
                        804,                  // номер
                        '10:35',              // время
                        null,                 // место
                        null,                 // очки
                        null,                 // выполненный разряд
                        true,
                    ],
                ],
                false,
                'openxmlformats'
            ],
        ];
    }
    protected function getAllGroups(): array
    {
        return [];
    }
    protected function getParser(): string
    {
        return ElkPathXlsxParser::class;
    }
}
