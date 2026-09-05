<?php

declare(strict_types=1);

namespace App\Application\Handler\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\PersonPrompt\CreatePersonPrompts;
use App\Application\Service\PersonPrompt\CreatePersonPromptsService;
use App\Domain\Person\Person;

readonly class AbstractCreatePersonPromptsHandler
{
    public function __construct(
        private CreatePersonPromptsService $service,
    ) {
    }

    public function process(Person $person): void
    {
        $this->service->execute(new CreatePersonPrompts(
            personId: $person->id,
            firstname: $person->firstname,
            lastname: $person->lastname,
            birthdayYear: $person->birthday?->format('Y'),
            userId: new UserId($person->updated->by),
        ));
    }
}
