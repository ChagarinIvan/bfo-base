<?php

namespace Tests\Integration\WinOrientParser;

use App\Models\Parser\WinOrientHtmlParser;
use Tests\Integration\AbstractParserTest;

class WinOrientParser2Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return WinOrientHtmlParser::class;
    }

    protected function getFilePath(): string
    {
        return '2019/protocol_190414_otkrytyy-kubok-g-grodno-2019_72884.htm';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Мурашка', //фаммилия
                'Усяслаў', //имя
                'СКО «Орион»', //клуб
                2007, //год
                'Iю', //разряд
                435, //номер
                '00:07:52', //время
                1, //место
                'IIю', //выполненный разряд
                80, //очки
            ],
            50 => [
                'Комаров',
                'Максим',
                'OK Kaliningrad RUS',
                2005,
                null,
                311,
                null,
                null,
                null,
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 411;
    }
}
