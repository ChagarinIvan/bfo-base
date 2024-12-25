<?php

declare(strict_types=1);

namespace App\Models\Parser;

use App\Models\Parser\List\CsvListParser;
use Illuminate\Support\Collection;
use RuntimeException;

class ParserFactory
{
    public const LIST_PARSERS = [
        CsvListParser::class,
    ];
    private const PROTOCOL_PARSERS = [
        ElkPathXlsxParser::class,
        XlsParser::class,
        OldObelarusNetXlsxParser::class,
        XlsxParser::class,
        SFRParser::class,
        HandicapAlbatrosTimingParser::class,
        AlbatrosRelayParser::class,
        AlbatrosRelayWithHeadersParser::class,
        AlbatrosTimingWithRegionParser::class,
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

    public static function createProtocolParser(string $protocol, Collection $groups, string $extension = 'html'): ParserInterface
    {
        foreach (self::PROTOCOL_PARSERS as $parser) {
            /** @var ParserInterface $parser */
            $parser = new $parser($groups);
            if ($parser->check($protocol, $extension)) {
                return $parser;
            }
        }

        throw new RuntimeException('нету подходящего парсера!!');
    }

    public static function createListParser(string $list, string $extension = 'csv'): ParserInterface
    {
        foreach (self::LIST_PARSERS as $parser) {
            /** @var ParserInterface $parser */
            $parser = new $parser();
            if ($parser->check($list, $extension)) {
                return $parser;
            }
        }
        throw new RuntimeException('нету подходящего парсера!!');
    }
}
