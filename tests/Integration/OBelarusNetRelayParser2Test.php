<?php

namespace Tests\Integration;

use App\Models\Parser\OBelarusNetRelayParser;

class OBelarusNetRelayParser2Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OBelarusNetRelayParser::class;
    }

    protected function getFilePath(): string
    {
        return '2021/210905_res.htm';
    }

    protected function getResults(): array
    {
        return [
            18 => [
                'Солодчук',
                'Дарья',
                'Гомельская обл.-2',
                2009,
                'б/р',
                1117,
                null,
                null,
                '-',
                null,
            ],
            27 => [
                'Ермолович',
                'Полина',
                'Минская область',
                2005,
                'б/р',
                1152,
                '01:01:53',
                1,
                null,
                291,
            ],
            222 => [
                'Маслакова',
                'Наталья',
                'КСО «Пеленг-Вымпел»',
                1954,
                null,
                1209,
                '01:35:12',
                10,
                null,
                null,
            ],
            223 => [
                'Ткачук',
                'Дарья',
                'КО «Сильван люкс»',
                1981,
                'I',
                1201,
                null,
                null,
                null,
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 225;
    }
}
