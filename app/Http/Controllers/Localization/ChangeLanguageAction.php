<?php

declare(strict_types=1);

namespace App\Http\Controllers\Localization;

use App\Http\Controllers\AbstractAction;
use App\Services\BackUrlService;
use App\Services\UserService;
use App\Services\ViewActionsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class ChangeLanguageAction extends AbstractAction
{
    private UserService $userService;

    public function __construct(
        ViewActionsService $viewService,
        Redirector $redirector,
        BackUrlService $backUrlService,
        UserService $userService,
    ) {
        parent::__construct($viewService, $redirector, $backUrlService);
        $this->userService = $userService;
    }

    public function __invoke(string $locale): RedirectResponse
    {
        $this->userService->setLocale($locale);
        $urlGenerator = $this->redirector->getUrlGenerator();
        return $this->redirector->to($urlGenerator->previous());
    }
}
