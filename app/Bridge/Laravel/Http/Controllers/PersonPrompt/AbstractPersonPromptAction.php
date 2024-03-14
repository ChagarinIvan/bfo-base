<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use App\Services\PersonPromptService;
use App\Services\ViewActionsService;
use Illuminate\Routing\Redirector;

class AbstractPersonPromptAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewService,
        protected Redirector $redirector,
        protected readonly PersonPromptService $promptService
    ) {
        parent::__construct($viewService, $redirector);
    }
}
