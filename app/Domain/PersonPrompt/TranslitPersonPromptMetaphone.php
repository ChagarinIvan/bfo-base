<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

use Mav\Slovo\Phonetics;
use RuntimeException;
use Transliterator;

final readonly class TranslitPersonPromptMetaphone implements PersonPromptMetaphone
{
    private Transliterator $transliterator;

    public function __construct(private Phonetics $phonetics)
    {
        $this->transliterator = Transliterator::create('Latin-Cyrillic')
            ?? throw new RuntimeException('Latin-Cyrillic transliterator is unavailable.');
    }

    public function calculate(string $prompt): string
    {
        return $this->phonetics->metaphour($this->transliterator->transliterate($prompt));
    }
}
