<?php

namespace App\Models\Parser;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class ParserFactory
{
    private const PARSERS = [
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

    public static function createParser(UploadedFile $file): ParserInterface
    {
        foreach (self::PARSERS as $parser) {
            $parser = new $parser();
            if ($parser->check($file)) {
                return $parser;
            }
        }
        throw new RuntimeException('Нету подходящего парсера!!');
    }
}
