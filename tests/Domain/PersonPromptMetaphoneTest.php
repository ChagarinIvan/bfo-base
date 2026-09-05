<?php

declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\PersonPrompt\TranslitPersonPromptMetaphone;
use Mav\Slovo\Phonetics;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PersonPromptMetaphoneTest extends TestCase
{
    #[Test]
    public function it_transliterates_latin_and_mixed_names_before_calculation(): void
    {
        $metaphone = new TranslitPersonPromptMetaphone(new Phonetics());

        $this->assertSame(
            $metaphone->calculate('александр_абрамов_1990'),
            $metaphone->calculate('аlеksаndr_аbrаmоv_1990'),
        );
        $this->assertNotSame('', $metaphone->calculate('alexandr_abramov_1990'));
    }
}
