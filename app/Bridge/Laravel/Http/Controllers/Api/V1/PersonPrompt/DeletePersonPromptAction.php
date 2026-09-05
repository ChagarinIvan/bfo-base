<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPrompt;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\PersonPrompt\DeletePersonPrompt;
use App\Application\Service\PersonPrompt\DeletePersonPromptService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Controllers\ResponseStatus;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

#[ResponseStatus(204)]
final class DeletePersonPromptAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $promptId, UserId $userId, DeletePersonPromptService $service): Response
    {
        $service->execute(new DeletePersonPrompt($promptId, $userId));

        return response()->noContent();
    }
}
