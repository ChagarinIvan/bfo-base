<?php

namespace Tests\Integration\WinOrientParser;

use App\Models\Parser\WinOrientHtmlParser;
use Tests\Integration\AbstractParserTest;

class WinOrientParser3Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return WinOrientHtmlParser::class;
    }

    protected function getFilePath(): string
    {
        return '2021/_210919_gp_res.htm';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Катлерова', //фамилия
                'Надежда',   //имя
                'КО «БГУ»',  //клуб
                1981,        //год
                'б/р',       //разряд
                9004,        //номер
                '00:21:43',  //время
                1,           //место
                '-',         //выполненный разряд
                null,        //очки
            ],
            72 => [
                'Кравченко',
                'Екатерина',
                'КО «Случь»',
                1976,
                'б/р',
                4058,
                null,
                null,
                '-',
                null,
            ],
            360 => [
                'Пунько',
                'Прохор',
                'Брестская обл',
                2009,
                'IIю',
                1261,
                '00:07:51',
                3,
                'IIю',
                null,
            ],
            371 => [
                'Барановский',
                'Павел',
                'Гродненская обл',
                2007,
                'б/р',
                1257,
                '00:09:44',
                null,
                '-',
                null,
                true,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 411;
    }
}
