<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\PersonPrompt\PersonPromptDto;
use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\UpdatePersonPrompt;
use App\Application\Service\PersonPrompt\UpdatePersonPromptService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class UpdatePersonPromptAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $promptId, PersonPromptDto $prompt, UpdatePersonPromptService $service, UserId $userId): ViewPersonPromptDto
    {
        return $service->execute(new UpdatePersonPrompt($prompt, $promptId, $userId));
    }
}
