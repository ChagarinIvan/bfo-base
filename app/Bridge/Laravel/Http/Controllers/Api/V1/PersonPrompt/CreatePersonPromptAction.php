<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPrompt\PersonPromptDto;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\AddPersonPrompt;
use App\Application\Service\PersonPrompt\AddPersonPromptService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Controllers\ResponseStatus;
use Illuminate\Routing\Controller as BaseController;

#[ResponseStatus(201)]
final class CreatePersonPromptAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $personId, PersonPromptDto $prompt, AddPersonPromptService $service, UserId $userId): ViewPersonPromptDto
    {
        return $service->execute(new AddPersonPrompt($prompt, $personId, $userId));
    }
}
