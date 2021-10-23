<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Controllers\BackAction;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;

class ViewActionsService
{
    private ViewFactory $viewFactory;
    private UserService $userService;
    private BackUrlService $backUrlService;
    private UrlGenerator $urlGenerator;
    private ExceptionHandler $exceptionHandler;

    public function __construct(
        ViewFactory $viewFactory,
        UserService $userService,
        BackUrlService $backUrlService,
        UrlGenerator $urlGenerator,
        ExceptionHandler $exceptionHandler,
    ) {
        $this->viewFactory = $viewFactory;
        $this->userService = $userService;
        $this->backUrlService = $backUrlService;
        $this->urlGenerator = $urlGenerator;
        $this->exceptionHandler = $exceptionHandler;
    }

    public function cleanBackUrls(): void
    {
        $this->backUrlService->clean();
    }

    public function generatePreviousUrl(): string
    {
        return $this->urlGenerator->previous();
    }

    public function makeBackAction(): string
    {
        return $this->urlGenerator->action(BackAction::class);
    }

    public function pushUrlInBackUrlsQueue(string $url): void
    {
        $this->backUrlService->push($url);
    }

    public function makeView(string $template, array $data, array $navbarData): View
    {
        return $this->viewFactory->make($template, $data, $navbarData);
    }

    public function isByLocale(): bool
    {
        return $this->userService->isByLocale();
    }

    public function isRuLocale(): bool
    {
        return $this->userService->isRuLocale();
    }

    public function isAuth(): bool
    {
        return $this->userService->isAuth();
    }

    public function getLastBackUrl(): string
    {
        return $this->backUrlService->pop();
    }

    public function handleException(\Exception $e): void
    {
        $this->exceptionHandler->report($e);
    }
}
