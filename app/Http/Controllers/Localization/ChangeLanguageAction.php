<?php

declare(strict_types=1);

namespace App\Http\Controllers\Localization;

use App\Http\Controllers\AbstractRedirectAction;
use App\Services\BackUrlService;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class ChangeLanguageAction extends AbstractRedirectAction
{
    private UserService $userService;

    public function __construct(
        Redirector $redirector,
        BackUrlService $backUrlService,
        UserService $userService
    ) {
        parent::__construct($redirector, $backUrlService);
        $this->userService = $userService;
    }

    public function __invoke(string $locale): RedirectResponse
    {
        $this->userService->setLocale($locale);
        $urlGenerator = $this->redirector->getUrlGenerator();
        return $this->redirector->to($urlGenerator->previous());
    }
}
