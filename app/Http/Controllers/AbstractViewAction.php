<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use \Illuminate\Contracts\View\Factory as ViewFactory;
use JetBrains\PhpStorm\ArrayShape;

abstract class AbstractViewAction extends Controller
{
    protected ViewFactory $viewFactory;
    private UserService $userService;

    public function __construct(ViewFactory $viewFactory, UserService $userService)
    {
        $this->viewFactory = $viewFactory;
        $this->userService = $userService;
    }

    protected function view(string $template, array $data = []): View
    {
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
}
