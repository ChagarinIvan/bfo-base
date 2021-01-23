<?php

declare(strict_types=1);

namespace Tests\Integration\HandicapAlbatrosTimingParser;

use App\Models\Parser\HandicapAlbatrosTimingParser;
use Tests\Integration\AbstractParserTest;

class HandicapTimingParser2Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return HandicapAlbatrosTimingParser::class;
    }

    protected function getFilePath(): string
    {
        return '2019/protocol_190929_bgu-98_59449.htm';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Малалетников',
                'Павел',
                'Лично',
                1982,
                'б/р',
                3102,
                '2:00:59',
                1,
                null,
                null,
            ],
            2 => [
                'Стебеняева',
                'Алёна',
                'КО «Легенда»',
                1974,
                'б/р',
                3105,
                null,
                null,
                null,
                null,
            ],
            79 => [
                'Михалкин',
                'Дмитрий',
                'КСО «Эридан»',
                1980,
                'МС',
                2010,
                '1:22:53',
                1,
                'КМС',
                null
            ],
            90 => [
                'Лычков',
                'Игорь',
                'КСО «Немига-Норд»',
                1983,
                'КМС',
                2006,
                null,
                null,
                null,
                null
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 114;
    }
}
