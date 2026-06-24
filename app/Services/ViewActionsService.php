<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\ErrorMail;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Throwable;

class ViewActionsService
{
    public function __construct(
        readonly private ViewFactory $viewFactory,
        readonly private UserService $userService,
        readonly private UrlGenerator $urlGenerator,
        readonly private Mailer $mailer,
    ) {
    }

    public function generatePreviousUrl(): string
    {
        return $this->urlGenerator->previous();
    }

    public function makeView(string $template, array $data, array $navbarData): View
    {
        return $this->viewFactory->make($template, $data, $navbarData);
    }

    public function isByLocale(): bool
    {
        //        return $this->userService->isByLocale();
        return true;
    }

    public function isRuLocale(): bool
    {
        //        return $this->userService->isRuLocale();
        return false;
    }

    public function isAuth(): bool
    {
        return $this->userService->isAuth();
    }

    public function sendErrorMail(Throwable $exception, string $url, string $previousUrl): void
    {
        $this->mailer->send(new ErrorMail($exception, $url, $previousUrl));
    }
}
