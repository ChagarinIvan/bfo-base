<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPrompt;

use App\Application\Dto\PersonPrompt\ViewPersonPromptDto;
use App\Application\Service\PersonPrompt\ViewPersonPrompt;
use App\Application\Service\PersonPrompt\ViewPersonPromptService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ViewPersonPromptAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $promptId, ViewPersonPromptService $service): ViewPersonPromptDto
    {
        return $service->execute(new ViewPersonPrompt($promptId));
    }
}
