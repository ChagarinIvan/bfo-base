<?php

declare(strict_types=1);

namespace Tests\Models\Parser;

use App\Models\Group;
use App\Models\Parser\ParserFactory;
use App\Models\ProtocolLine;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Collection;
use Tests\TestCase;

abstract class AbstractParser extends TestCase
{
    abstract public static function dataProvider(): array;
    abstract protected function getParser(): string;

    /**
     * @dataProvider dataProvider
     *
     * @test
     */
    public function parse(string $filePath, int $linesCount, array $expectedResults, bool $needConvert = false, string $extension = 'html'): void
    {
        $storageManager = new FilesystemManager($this->app);
        $protocolContent = $storageManager->disk('tests')->get($filePath);

        $parserClass = $this->getParser();

        $parser = ParserFactory::createProtocolParser($protocolContent, Collection::make($this->getAllGroups())->pluck('name'), $extension);
        self::assertInstanceOf($parserClass, $parser);

        $lines = $parser->parse($protocolContent, $needConvert);
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
            self::assertEquals($expectedResult[6] === null ? null : Carbon::createFromTimeString($expectedResult[6]), $line['time'] ?? null, 'time');
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

    protected function getAllGroups(): array
    {
        $group1 = new Group();
        $group1->name = 'М12';
        $group2 = new Group();
        $group2->name = 'Ж12';
        $group3 = new Group();
        $group3->name = 'М14';
        $group4 = new Group();
        $group4->name = 'Ж14';
        $group5 = new Group();
        $group5->name = 'М16';
        $group6 = new Group();
        $group6->name = 'Ж16';
        $group7 = new Group();
        $group7->name = 'М18';
        $group8 = new Group();
        $group8->name = 'Ж18';

        return [$group1, $group2, $group3, $group4, $group5, $group6, $group7, $group8];
    }
}
