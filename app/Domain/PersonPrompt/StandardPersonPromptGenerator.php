<?php

declare(strict_types=1);

namespace App\Domain\PersonPrompt;

use App\Domain\Shared\NameNormalizer;
use function array_merge;
use function implode;
use function mb_strtolower;

final readonly class StandardPersonPromptGenerator implements PersonPromptGenerator
{
    public function __construct(private NameNormalizer $normalizer)
    {
    }

    /** @return list<string> */
    public function generate(string $firstname, string $lastname, ?string $birthdayYear = null, bool $hasNamesake = false): array
    {
        $lastname = $this->normalizer->normalize(mb_strtolower($lastname));
        $firstname = $this->normalizer->normalize(mb_strtolower($firstname));

        $name = [$lastname, $firstname];
        $reversedName = [$firstname, $lastname];

        if ($hasNamesake) {
            return $birthdayYear === null ? [] : [
                implode('_', [...$name, $birthdayYear]),
                implode('_', [...$reversedName, $birthdayYear]),
            ];
        }

        return array_merge(
            [implode('_', $name), implode('_', $reversedName)],
            $birthdayYear === null ? [] : [
                implode('_', [...$name, $birthdayYear]),
                implode('_', [...$reversedName, $birthdayYear]),
            ],
        );
    }
}
