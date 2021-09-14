<?php

namespace App\Models\Parser;

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

    public static function createParser(string $protocol): ParserInterface
    {
        foreach (self::PARSERS as $parser) {
            $parser = new $parser();
            if ($parser->check($protocol)) {
                return $parser;
            }
        }
        throw new \RuntimeException('нету подходящего парсера!!');
    }
}
