<?php

namespace Tests\Integration\OBelarusNetRelayParser;

use App\Models\Parser\OBelarusNetRelayParser;
use Tests\Integration\AbstractParserTest;

class OBelarusNetRelayParser3Test extends AbstractParserTest
{
    protected function getParser(): string
    {
        return OBelarusNetRelayParser::class;
    }

    protected function getFilePath(): string
    {
        return '2021/20210717.html';
    }

    protected function getResults(): array
    {
        return [
            0 => [
                'Полина',
                'Котова',
                'КСА Каданьётта',
                null,
                null,
                1004,
                '01:27:26',
                1,
                null,
                null,
            ],
            12 => [
                'Владимир',
                'Зеленин',
                'КСА Каданьётта',
                null,
                null,
                1002,
                null,
                null,
                null,
                null,
            ],
        ];
    }

    protected function geLinesCount(): int
    {
        return 38;
    }

    protected function needConvert(): bool
    {
        return false;
    }
}
