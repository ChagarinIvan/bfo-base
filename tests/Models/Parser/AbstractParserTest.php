<?php

namespace Tests\Models\Parser;

use App\Models\Parser\ParserFactory;
use App\Models\ProtocolLine;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Collection;
use Tests\TestCase;

abstract class AbstractParserTest extends TestCase
{
    abstract protected function getParser(): string;
    abstract public function testData(): array;

    /**
     * @dataProvider testData
     */
    public function testParse(string $filePath, int $linesCount, array $expectedResults): void
    {
        $storageManager = new FilesystemManager($this->app);
        $protocolContent = $storageManager->disk('tests')->get($filePath, );

        $parserClass = $this->getParser();
        $parser = ParserFactory::createProtocolParser($protocolContent, new Collection());
        self::assertInstanceOf($parserClass, $parser);

        $lines = $parser->parse($protocolContent, $this->needConvert());
        self::assertCount($linesCount, $lines);

        foreach ($expectedResults as $index => $expectedResult) {
            /** @var ProtocolLine $line */
            $line = $lines->get($index);
            self::assertEquals($expectedResult[0], $line['lastname'], 'lastname');
            self::assertEquals($expectedResult[1], $line['firstname'], 'firstname');
            self::assertEquals($expectedResult[2], $line['club'], 'club');
            self::assertEquals($expectedResult[3], $line['year'] ?? null, 'year');
            self::assertEquals($expectedResult[4], $line['rank'] ?? null, 'rank');
            self::assertEquals($expectedResult[5], $line['runner_number'], 'runner_number');
            self::assertEquals($expectedResult[6] === null ? null : Carbon::createFromTimeString($expectedResult[6]), $line['time'], 'time');
            self::assertEquals($expectedResult[7], $line['place'], 'place');
            self::assertEquals($expectedResult[8] ?? null, $line['complete_rank'] ?? null, 'complete_rank');
            self::assertEquals($expectedResult[9] ?? null, $line['points'] ?? null, 'points');
            if (isset($expectedResult[10])) {
                self::assertEquals($expectedResult[10], $line['vk'] ?? false, 'vk');
            }
            if (isset($expectedResult[11])) {
                self::assertEquals($expectedResult[11], $line['group'], 'group');
            }
        }
    }

    protected function needConvert(): bool
    {
        return false;
    }
}
