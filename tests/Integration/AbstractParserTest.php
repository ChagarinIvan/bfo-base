<?php

namespace Tests\Integration;

use App\Models\Parser\ParserFactory;
use App\Models\ProtocolLine;
use Carbon\Carbon;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

abstract class AbstractParserTest extends TestCase
{
    abstract protected function getParser(): string;
    abstract protected function getFilePath(): string;
    abstract protected function getResults(): array;
    abstract protected function geLinesCount(): int;

    public function testParse()
    {
        $storageManager = new FilesystemManager($this->app);
        $protocolContent = $storageManager->disk('tests')->get($this->getFilePath());
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
            self::assertEquals($result[0], $line['lastname'], 'lastname');
            self::assertEquals($result[1], $line['firstname'], 'firstname');
            self::assertEquals($result[2], $line['club'], 'club');
            self::assertEquals($result[3], $line['year'] ?? null, 'year');
            self::assertEquals($result[4], $line['rank'] ?? null, 'rank');
            self::assertEquals($result[5], $line['runner_number'], 'runner_number');
            self::assertEquals($result[6] === null ? null : Carbon::createFromTimeString($result[6]), $line['time'], 'time');
            self::assertEquals($result[7], $line['place'], 'place');
            self::assertEquals($result[8], $line['complete_rank'] ?? null, 'complete_rank');
            self::assertEquals($result[9], $line['points'] ?? null, 'points');
            if (isset($result[10])) {
                self::assertEquals($result[10], $line['vk'] ?? false, 'vk');
            }
            if (isset($result[11])) {
                self::assertEquals($result[11], $line['group'], 'group');
            }
        }
    }
}
