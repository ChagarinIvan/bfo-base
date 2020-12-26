<?php

namespace Tests\Integration;

use App\Models\Parser\ParserFactory;
use App\Models\ProtocolLine;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

abstract class AbstractParserTest extends TestCase
{
    abstract protected function getParser(): string;
    abstract protected function getFilePath(): string;
    abstract protected function getResults(): array;
    abstract protected function geLinesCount(): int;

    public function testParse()
    {
        $protocolContent = Storage::get($this->getFilePath());
        $protocolFactory = UploadedFile::fake();
        $protocol = $protocolFactory->createWithContent('test', $protocolContent);

        $parserClass = $this->getParser();
        $parser = ParserFactory::createParser($protocol);
        self::assertInstanceOf($parserClass, $parser);

        $lines = $parser->parse($protocol);
        self::assertCount($this->geLinesCount(), $lines);
        $results = $this->getResults();
        foreach ($results as $index => $result) {
            /** @var ProtocolLine $line */
            $line = $lines->get($index);
            self::assertEquals($result[0], $line['lastname']);
            self::assertEquals($result[1], $line['firstname']);
            self::assertEquals($result[2], $line['club']);
            self::assertEquals($result[3], $line['year']);
            self::assertEquals($result[4], $line['rank']);
            self::assertEquals($result[5], $line['runner_number']);
            self::assertEquals(Carbon::createFromTimeString($result[6]), $line['time']);
            self::assertEquals($result[7], $line['place']);
            self::assertEquals($result[8], $line['complete_rank']);
            self::assertEquals($result[9], $line['points']);
        }
    }
}
