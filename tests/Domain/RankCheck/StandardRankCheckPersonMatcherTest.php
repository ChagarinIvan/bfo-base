<?php

declare(strict_types=1);

namespace Tests\Domain\RankCheck;

use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\RankCheck\StandardRankCheckPersonMatcher;
use App\Domain\Shared\Criteria;
use App\Services\ProtocolLineIdentService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class StandardRankCheckPersonMatcherTest extends TestCase
{
    private MockObject&ProtocolLineIdentService $identification;

    private MockObject&PersonPromptRepository $personPrompts;

    private StandardRankCheckPersonMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->matcher = new StandardRankCheckPersonMatcher(
            $this->personPrompts = $this->createMock(PersonPromptRepository::class),
            $this->identification = $this->createMock(ProtocolLineIdentService::class),
        );
    }

    #[Test]
    public function it_uses_direct_matches_before_similarity_search(): void
    {
        $this->personPrompts
            ->expects($this->once())
            ->method('byCriteria')
            ->with(new Criteria(['prompts' => ['ivanov_ivan_2000', 'petrov_petr_2001']]))
            ->willReturn(new Collection([(object) ['prompt' => 'ivanov_ivan_2000', 'person_id' => 11]]))
        ;
        $this->identification
            ->expects($this->once())
            ->method('identPerson')
            ->with('petrov_petr_2001')
            ->willReturn(12)
        ;

        $this->assertSame([
            'ivanov_ivan_2000' => 11,
            'petrov_petr_2001' => 12,
        ], $this->matcher->match(['ivanov_ivan_2000', 'petrov_petr_2001']));
    }

    #[Test]
    public function it_skips_lines_without_a_person_match(): void
    {
        $this->personPrompts
            ->expects($this->once())
            ->method('byCriteria')
            ->with(new Criteria(['prompts' => ['unknown']]))
            ->willReturn(new Collection)
        ;
        $this->identification
            ->expects($this->once())
            ->method('identPerson')
            ->with('unknown')
            ->willReturn(0)
        ;

        $this->assertSame([], $this->matcher->match(['unknown']));
    }
}
