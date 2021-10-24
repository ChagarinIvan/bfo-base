<?php

namespace App\Models\Parser;

use Illuminate\Support\Collection;

class ParserFactory
{
    private const PARSERS = [
        SFRParser::class,
        HandicapAlbatrosTimingParser::class,
        AlbatrosRelayParser::class,
        AlbatrosTimingParser::class,
        OBelarusNetRelayWithHeadersParser::class,
        OBelarusNetRelayParser::class,
        WinOrientHtmlParser::class,
        SimplyParser::class,
        OParser::class,
        OBelarusNetParser::class,
    ];

    public static function createParser(string $protocol, Collection $groups): ParserInterface
    {
        foreach (self::PARSERS as $parser) {
            $parser = new $parser($groups);
            if ($parser->check($protocol)) {
                return $parser;
            }
        }
        throw new \RuntimeException('нету подходящего парсера!!');
    }
}
