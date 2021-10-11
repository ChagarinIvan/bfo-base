<?php

namespace Tests\Integration\WinOrientParser;

use App\Models\Parser\WinOrientHtmlParser;
use Tests\Integration\AbstractParserTest;

class WinOrientParser4Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return WinOrientHtmlParser::class;
    }

    protected function getFilePath(): string
    {
        return '2021/211009_res.htm';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Борзина', //фамилия
                'Ульяна',   //имя
                'Эридан 2',  //клуб
                2012,        //год
                'б/р',       //разряд
                159,        //номер
                '00:14:07',  //время
                1,           //место
                '-',         //выполненный разряд
                null,        //очки
            ],
            6 => [
                'Миронова',
                'Анастасия',
                'Эридан 1',
                2013,
                'б/р',
                155,
                null,
                null,
                '-',
                null,
            ],
            146 => [
                'Шавель',
                'Ярослав',
                'ДДиМ Новополоцка 4',
                2006,
                'б/р',
                96,
                null,
                null,
                '-',
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 166;
    }
}
