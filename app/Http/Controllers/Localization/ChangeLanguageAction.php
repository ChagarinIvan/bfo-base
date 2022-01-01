<?php

namespace App\Http\Controllers\Localization;

use App\Http\Controllers\AbstractAction;
use App\Services\UserService;
use App\Services\ViewActionsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class ChangeLanguageAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewService,
        protected Redirector $redirector,
        private UserService $userService
    ) {
        parent::__construct($viewService, $redirector);
    }

    public function __invoke(string $locale): RedirectResponse
    {
        $this->userService->setLocale($locale);
        $urlGenerator = $this->redirector->getUrlGenerator();
        return $this->redirector->to($urlGenerator->previous());
    }
}
