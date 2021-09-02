<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

abstract class AbstractViewAction extends Controller
{
    private ViewActionsService $viewService;

    public function __construct(ViewActionsService $viewService)
    {
        $this->viewService = $viewService;
    }

    protected function view(string $template, array $data = []): View
    {
        if ($this->isNavbarRoute()) {
            $this->viewService->cleanBackUrls();
        } else {
            $previous = $this->viewService->generatePreviousUrl();
            $backUrl = $this->viewService->makeBackAction();
            if ($previous !== $backUrl) {
                $this->viewService->pushUrlInBackUrlsQueue($previous);
            }
        }

        return $this->viewService->makeView($template, $data, $this->navbarData());
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
