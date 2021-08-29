<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BackUrlService;
use App\Services\UserService;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use \Illuminate\Contracts\View\Factory as ViewFactory;

abstract class AbstractViewAction extends Controller
{
    protected ViewFactory $viewFactory;
    private UserService $userService;
    private BackUrlService $backUrlService;
    private UrlGenerator $urlGenerator;

    public function __construct(ViewFactory $viewFactory, UserService $userService, BackUrlService $backUrlService, UrlGenerator $urlGenerator)
    {
        $this->viewFactory = $viewFactory;
        $this->userService = $userService;
        $this->backUrlService = $backUrlService;
        $this->urlGenerator = $urlGenerator;
    }

    protected function view(string $template, array $data = []): View
    {
        if ($this->isNavbarRoute()) {
            $this->backUrlService->clean();
        } else {
            $previous = $this->urlGenerator->previous();
            $backUrl = $this->urlGenerator->action(BackAction::class);
            if ($previous !== $backUrl) {
                $this->backUrlService->push($previous);
            }
        }

        return $this->viewFactory->make($template, $data, $this->navbarData());
    }

    private function navbarData(): array
    {
        return [
            'isAuth' => $this->userService->isAuth(),
            'isByLocale' => $this->userService->isByLocale(),
            'isRuLocale' => $this->userService->isRuLocale(),
            'isCompetitionsRoute' => $this->isCompetitionsRoute(),
            'isCupsRoute' => $this->isCupsRoute(),
            'isPersonsRoute' => $this->isPersonsRoute(),
            'isClubsRoute' => $this->isClubsRoute(),
            'isFlagsRoute' => $this->isFlagsRoute(),
            'isFaqRoute' => $this->isFaqRoute(),
            'isFaqApiRoute' => $this->isFaqApiRoute(),
        ];
    }

    protected function isCompetitionsRoute(): bool
    {
        return false;
    }

    protected function isCupsRoute(): bool
    {
        return false;
    }

    protected function isPersonsRoute(): bool
    {
        return false;
    }

    protected function isClubsRoute(): bool
    {
        return false;
    }

    protected function isFlagsRoute(): bool
    {
        return false;
    }

    protected function isFaqRoute(): bool
    {
        return false;
    }

    protected function isFaqApiRoute(): bool
    {
        return false;
    }

    protected function isNavbarRoute(): bool
    {
        return false;
    }
}
