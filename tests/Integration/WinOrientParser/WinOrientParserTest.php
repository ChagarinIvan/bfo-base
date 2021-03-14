<?php

namespace Tests\Integration\WinOrientParser;

use App\Models\Parser\WinOrientHtmlParser;
use Tests\Integration\AbstractParserTest;

class WinOrientParserTest extends AbstractParserTest
{
    protected function getParser(): string
    {
        return WinOrientHtmlParser::class;
    }

    protected function getFilePath(): string
    {
        return '2020/18072020.htm';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Михалкин', //фаммилия
                'Игорь', //имя
                'Эридан', //клуб
                2008, //год
                null, //разряд
                141, //номер
                '00:27:39', //время
                1, //место
                null, //очки
                null, //выполненный разряд
            ],
            14 => [
                'Голютов',
                'Никита',
                'Лагерь "Волобо"',
                null,
                null,
                36,
                null,
                null,
                null,
                null,
            ],
            45 => [
                'Языков',
                'Юрий',
                'КСО "Немига-Норд"',
                1990,
                'МСМК',
                150,
                '00:42:10',
                5,
                null,
                null,
            ],
            46 => [
                'Попов',
                'Дмитрий',
                'Эридан',
                1999,
                'КМС',
                191,
                '00:42:12',
                6,
                null,
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 219;
    }
}
