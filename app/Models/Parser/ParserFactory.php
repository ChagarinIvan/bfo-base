<?php

namespace App\Models\Parser;

use App\Models\Parser\List\CsvListParser;
use Illuminate\Support\Collection;

class ParserFactory
{
    private const PROTOCOL_PARSERS = [
        XlsParser::class,
        SFRParser::class,
        HandicapAlbatrosTimingParser::class,
        AlbatrosRelayParser::class,
        AlbatrosTimingParser::class,
        OBelarusNetRelayWithHeadersParser::class,
        OBelarusNetRelayParser::class,
        WinOrientHtmlParser::class,
        SimplyParser::class,
        OParser::class,
        HrodnoParser::class,
        OBelarusSpanParser::class,
        OBelarusNetParser::class,
    ];

    public const LIST_PARSERS = [
        CsvListParser::class,
    ];

    public static function createProtocolParser(string $protocol, Collection $groups, string $extension = 'html'): ParserInterface
    {
        foreach (self::PROTOCOL_PARSERS as $parser) {
            /** @var ParserInterface $parser */
            $parser = new $parser($groups);
            if ($parser->check($protocol, $extension)) {
                return $parser;
            }
        }
        throw new \RuntimeException('нету подходящего парсера!!');
    }

    public static function createListParser(string $list): ParserInterface
    {
        foreach (self::LIST_PARSERS as $parser) {
            $parser = new $parser();
            if ($parser->check($list)) {
                return $parser;
            }
        }
        throw new \RuntimeException('нету подходящего парсера!!');
    }
}
