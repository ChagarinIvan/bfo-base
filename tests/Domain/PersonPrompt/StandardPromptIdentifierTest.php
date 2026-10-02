<?php

declare(strict_types=1);

namespace Tests\Domain\PersonPrompt;

use App\Domain\PersonPrompt\PersonPromptMetaphone;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\PersonPrompt\StandardPromptIdentifier;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function array_map;

final class StandardPromptIdentifierTest extends TestCase
{
    #[Test]
    public function it_loads_the_prompt_index_once_and_selects_the_nearest_prompt(): void
    {
        $identifier = $this->identifier('ABC', [
            ['prompt' => 'runner_2000', 'metaphone' => 'ABC', 'person_id' => 11],
            ['prompt' => 'runner_2001', 'metaphone' => 'ABC', 'person_id' => 12],
        ]);

        $this->assertSame(12, $identifier->identPerson('runner_2001'));
        $this->assertSame(11, $identifier->identPerson('runner_2000'));
    }

    #[Test]
    public function it_selects_the_nearest_phonetic_bucket_before_comparing_prompts(): void
    {
        $identifier = $this->identifier('ABC', [
            ['prompt' => 'runner_2000', 'metaphone' => 'AXD', 'person_id' => 11],
            ['prompt' => 'runner_2001', 'metaphone' => 'ABD', 'person_id' => 12],
        ]);

        $this->assertSame(12, $identifier->identPerson('runner_2000'));
    }

    #[Test]
    #[DataProvider('distanceCases')]
    public function it_respects_both_distance_limits(string $prompt, string $metaphone, int $expected): void
    {
        $identifier = $this->identifier('ABC', [
            ['prompt' => $prompt, 'metaphone' => $metaphone, 'person_id' => 11],
        ]);

        $this->assertSame($expected, $identifier->identPerson('runner'));
    }

    /** @return array<string, array{string, string, int}> */
    public static function distanceCases(): array
    {
        return [
            'phonetic distance two' => ['runner', 'AXD', 11],
            'phonetic distance three' => ['runner', 'XYZ', 0],
            'prompt distance five' => ['runner12345', 'ABC', 11],
            'prompt distance six' => ['runner123456', 'ABC', 0],
        ];
    }

    #[Test]
    public function it_returns_zero_when_there_are_no_prompts(): void
    {
        $this->assertSame(0, $this->identifier('ABC', [])->identPerson('runner'));
    }

    #[Test]
    public function each_identifier_instance_loads_its_own_prompt_snapshot(): void
    {
        $first = $this->identifier('ABC', [
            ['prompt' => 'runner', 'metaphone' => 'ABC', 'person_id' => 11],
        ]);
        $second = $this->identifier('ABC', [
            ['prompt' => 'runner', 'metaphone' => 'ABC', 'person_id' => 12],
        ]);

        $this->assertSame(11, $first->identPerson('runner'));
        $this->assertSame(12, $second->identPerson('runner'));
    }

    /** @param list<array{prompt: string, metaphone: string, person_id: int}> $prompts */
    private function identifier(string $metaphoneValue, array $prompts): StandardPromptIdentifier
    {
        $repository = $this->createMock(PersonPromptRepository::class);
        $repository->expects($this->once())->method('byCriteria')
            ->with(Criteria::empty())
            ->willReturn(new Collection(array_map(static fn (array $prompt): object => (object) $prompt, $prompts)));
        $metaphone = $this->createStub(PersonPromptMetaphone::class);
        $metaphone->method('calculate')->willReturn($metaphoneValue);

        return new StandardPromptIdentifier($repository, $metaphone);
    }
}
