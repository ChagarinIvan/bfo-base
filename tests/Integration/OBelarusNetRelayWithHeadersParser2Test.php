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
                null,
                null,
            ],
            4 => [
                'Сукневич',
                'София',
                'Минск',
                null,
                null,
                153,
                '00:13:11',
                null,
                null,
                null,
                true
            ],
            38 => [
                'Синчикова',
                'Виктория',
                'Гомельская область',
                null,
                'II',
                143,
                '00:13:29',
                1,
                'Iю',
                291,
            ],
            66 => [
                'Вольщук',
                'Максим',
                'ГОЦТиК КСО "Верас"',
                null,
                null,
                146,
                '00:18:23',
                null,
                null,
                null,
            ],
            67 => [
                'Лебецкий',
                'Назар',
                'ГОЦТиК КСО "Верас"',
                null,
                null,
                246,
                null,
                null,
                null,
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 272;
    }
}
