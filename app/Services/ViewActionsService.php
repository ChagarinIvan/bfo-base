<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;

final readonly class ViewActionsService
{
    public function __construct(
        private ViewFactory $viewFactory,
        private UserService $userService,
    ) {
    }

    public function makeView(string $template, array $data, array $navbarData): View
    {
        return $this->viewFactory->make($template, $data, $navbarData);
    }

    public function isAuth(): bool
    {
        return $this->userService->isAuth();
    }
}
