<?php

declare(strict_types=1);

namespace Models\Parser;

use App\Models\Parser\OldObelarusNetXlsxParser;
use Tests\Models\Parser\AbstractParser;

final class OldObelarusNetXlsxParserTest extends AbstractParser
{
    public static function dataProvider(): array
    {
        return [
            [
                '2024/20251225.xlsx',
                88,
                [
                    0 => [
                        'Михалкин',     //фамилия
                        'Дмитрий',    //имя
                        'Маяк',   //клуб
                        1980,        //год
                        'МС',       //разряд
                        43,           //номер
                        '1:22:19',   //время
                        1,           //место
                        'МС',       //выполненный разряд
                        100,
                    ],
                    52 => [
                        'Денисов',     //фамилия
                        'Валерий',    //имя
                        'Белая Русь',   //клуб
                        1963,        //год
                        'КМС',       //разряд
                        30,          //номер
                        null,        //время
                        '-',        //место
                        null,        //выполненный разряд
                        null,        //очки
                    ],
                    63 => [
                        'Петрова',     //фамилия
                        'Оксана',    //имя
                        'Азимут',   //клуб
                        1966,        //год
                        'КМС',       //разряд
                        90,          //номер
                        '1:20:08',        //время
                        11,        //место
                        'I',        //выполненный разряд
                        77,        //очки
                    ],
                ],
                'openxmlformats'
            ],
        ];
    }
    protected function getParser(): string
    {
        return OldObelarusNetXlsxParser::class;
    }
}
