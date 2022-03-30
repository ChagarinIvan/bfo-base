<?php

namespace Tests\Integration;

use App\Models\Parser\OBelarusNetRelayWithHeadersParser;

class OBelarusNetRelayWithHeadersParser2Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OBelarusNetRelayWithHeadersParser::class;
    }

    protected function getFilePath(): string
    {
        return '2020/mix_rel_2020.html';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Власенко',
                'Арина',
                'ГОЦТиК КСО "Верас"',
                null,
                'IIю',
                149,
                '00:13:26',
                1,
                '-',
                null,
            ],
            4 => [
                'Сукневич',
                'София',
                'Минск',
                null,
                'IIю',
                153,
                '00:13:11',
                2,
                '-',
                null,
                true
            ],
            42 => [
                'Синчикова',
                'Виктория',
                'Гомельская область',
                null,
                'II',
                143,
                '00:13:29',
                1,
                'Iю',
                null,
            ],
            70 => [
                'Вольщук',
                'Максим',
                'ГОЦТиК КСО "Верас"',
                null,
                null,
                146,
                '00:18:23',
                null,
                '-',
                null,
            ],
            71 => [
                'Лебецкий',
                'Назар',
                'ГОЦТиК КСО "Верас"',
                null,
                null,
                246,
                null,
                null,
                '-',
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 277;
    }
}
