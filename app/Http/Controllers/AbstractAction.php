<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Error\Show404ErrorAction;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;

abstract class AbstractAction extends Controller
{
    private ViewActionsService $viewService;
    protected Redirector $redirector;

    public function __construct(
        ViewActionsService $viewService,
        Redirector $redirector
    ) {
        $this->viewService = $viewService;
        $this->redirector = $redirector;
    }

    protected function view(string $template, array $data = []): View
    {
        if ($this->isNavbarRoute()) {
            $this->viewService->cleanBackUrls();
        } else {
            if ($this::class !== $this->viewService->getActualAction()) {
                $previous = $this->viewService->generatePreviousUrl();
                $backUrl = $this->viewService->makeBackAction();
                if ($previous !== $backUrl) {
                    $this->viewService->pushUrlInBackUrlsQueue($previous);
                }
            }
            $this->viewService->setActualAction($this::class);
        }

        return $this->viewService->makeView($template, $data, $this->navbarData());
    }

    protected function removeLastBackUrl(): string
    {
        return $this->viewService->getLastBackUrl();
    }

    private function navbarData(): array
    {
        return [
            'isAuth' => $this->viewService->isAuth(),
            'isByLocale' => $this->viewService->isByLocale(),
            'isRuLocale' => $this->viewService->isRuLocale(),
            'isCompetitionsRoute' => $this->isCompetitionsRoute(),
            'isCupsRoute' => $this->isCupsRoute(),
            'isPersonsRoute' => $this->isPersonsRoute(),
            'isClubsRoute' => $this->isClubsRoute(),
            'isRanksRoute' => $this->isRanksRoute(),
            'isFlagsRoute' => $this->isFlagsRoute(),
            'isFaqRoute' => $this->isFaqRoute(),
            'isFaqApiRoute' => $this->isFaqApiRoute(),
            'isGroupsRoute' => $this->isGroupsRoute(),
        ];
    }

    protected function redirectToError(): RedirectResponse
    {
        return $this->redirector->action(Show404ErrorAction::class);
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

    protected function isRanksRoute(): bool
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

    protected function isGroupsRoute(): bool
    {
        return false;
    }

    protected function isNavbarRoute(): bool
    {
        return false;
    }
}