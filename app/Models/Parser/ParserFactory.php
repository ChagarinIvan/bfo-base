<?php

namespace App\Models\Parser;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class ParserFactory
{
    private const PARSER = [
        AlbatrosTimingParser::class,
        WinOrientHtmlParser::class,
        OBelarusNetRelayWithHeadersParser::class,
        OBelarusNetRelayParser::class,
        OBelarusNetParser::class,
    ];

    public static function createParser(UploadedFile $file, string $type = null): ParserInterface
    {
        foreach (self::PARSER as $parser) {
            $parser = new $parser();
            if ($parser->check($file, $type)) {
                return $parser;
            }
        }
        throw new RuntimeException('Нету подходящего парсера!!');
    }
}
