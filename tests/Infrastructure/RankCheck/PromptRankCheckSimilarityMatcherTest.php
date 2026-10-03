<?php

declare(strict_types=1);

namespace Tests\Infrastructure\RankCheck;

use App\Domain\PersonPrompt\PersonPromptMetaphone;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\Shared\Criteria;
use App\Infrastructure\RankCheck\PromptRankCheckSimilarityMatcher;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PromptRankCheckSimilarityMatcherTest extends TestCase
{
    #[Test]
    public function it_keeps_legacy_thresholds_and_first_match_ties_with_one_corpus_load(): void
    {
        $repository = $this->createMock(PersonPromptRepository::class);
        $repository->expects($this->once())->method('byCriteria')->with(Criteria::empty())->willReturn(new Collection([
            (object) ['prompt' => 'anna', 'metaphone' => 'AAA', 'person_id' => 11],
            (object) ['prompt' => 'anne', 'metaphone' => 'AAA', 'person_id' => 12],
            (object) ['prompt' => 'annx', 'metaphone' => 'AAB', 'person_id' => 13],
            (object) ['prompt' => 'bbbbbb', 'metaphone' => 'BBB', 'person_id' => 14],
        ]));
        $metaphone = $this->createMock(PersonPromptMetaphone::class);
        $metaphone->expects($this->exactly(5))->method('calculate')->willReturnMap([
            ['annx', 'AAC'],
            ['anna', 'AXX'],
            ['annz', 'XXX'],
            ['xxxxxx', 'BBB'],
            ['xxxxxb', 'BBB'],
        ]);

        $matches = new PromptRankCheckSimilarityMatcher($repository, $metaphone)->match([
            'annx', 'anna', 'annz', 'xxxxxx', 'xxxxxb', 'xxxxxx',
        ]);

        $this->assertSame(['annx' => 11, 'anna' => 11, 'xxxxxb' => 14], $matches);
    }

    #[Test]
    public function an_empty_corpus_returns_no_matches(): void
    {
        $repository = $this->createMock(PersonPromptRepository::class);
        $repository->expects($this->once())->method('byCriteria')->willReturn(new Collection());
        $metaphone = $this->createMock(PersonPromptMetaphone::class);
        $metaphone->expects($this->never())->method('calculate');

        $this->assertSame([], new PromptRankCheckSimilarityMatcher($repository, $metaphone)->match(['unknown']));
    }

    #[Test]
    public function an_empty_list_does_not_load_the_corpus(): void
    {
        $repository = $this->createMock(PersonPromptRepository::class);
        $repository->expects($this->never())->method('byCriteria');
        $metaphone = $this->createMock(PersonPromptMetaphone::class);
        $metaphone->expects($this->never())->method('calculate');

        $this->assertSame([], new PromptRankCheckSimilarityMatcher($repository, $metaphone)->match([]));
    }
}
