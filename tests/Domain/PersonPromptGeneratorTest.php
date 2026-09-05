<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\PersonPrompt\StandardPersonPromptGenerator;
use App\Domain\Shared\NameNormalizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PersonPromptGeneratorTest extends TestCase
{
    #[Test]
    public function it_generates_forward_reversed_and_birthday_prompts(): void
    {
        $normalizer = $this->createStub(NameNormalizer::class);
        $normalizer->method('normalize')->willReturnArgument(0);

        $prompts = new StandardPersonPromptGenerator($normalizer)->generate('Ivan', 'Petrov', '1990');

        $this->assertSame([
            'petrov_ivan',
            'ivan_petrov',
            'petrov_ivan_1990',
            'ivan_petrov_1990',
        ], $prompts);
    }

    #[Test]
    public function it_uses_only_birthday_prompts_for_namesakes(): void
    {
        $normalizer = $this->createStub(NameNormalizer::class);
        $normalizer->method('normalize')->willReturnArgument(0);

        $this->assertSame(['petrov_ivan_1990', 'ivan_petrov_1990'], new StandardPersonPromptGenerator($normalizer)->generate('Ivan', 'Petrov', '1990', true));
    }
}
