<?php
declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Parser\SFRParser;

class SfrParserTest extends AbstractParserTest
{
    public static function dataProvider(): array
    {
        return [
            [
                '2023/26.08.2023.htm',
                100,
                [
                    0 => [
                        'Уланов',
                        'Андрей',
                        'PULS-Nadejda',
                        null,
                        null,
                        290,
                        '00:17:08',
                        1,
                        null,
                        null,
                    ],
                ],
                true
            ],
        ];
    }
    protected function getParser(): string
    {
        return SFRParser::class;
    }
}
