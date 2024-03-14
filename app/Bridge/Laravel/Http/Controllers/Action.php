<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers;

use App\Application\Dto\AbstractDto;
use App\Bridge\Laravel\Http\Controllers\Error\Show404ErrorAction;
use App\Bridge\Laravel\Http\Controllers\Error\ShowUnexpectedErrorAction;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Validation\Factory as Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait Action
{
    public function __construct(
        private readonly ViewActionsService $viewService,
        protected readonly Redirector $redirector,
        private readonly Request $request,
        private readonly Validator $validator,
    ) {
    }

    public function callAction($method, $parameters): Response|View
    {
        $injectParams = [];
        foreach ($parameters as $parameter) {
            if ($parameter instanceof AbstractDto) {
                try {
                    if ($parameter->fromRequest()) {
                        $validated = $this->request->validate($parameter::validationRules());
                    } else {
                        $validated = $this->validator->validate($parameters, $parameter::validationRules());
                    }
                } catch (ValidationException $e) {
                    throw new BadRequestHttpException($e->getMessage(), previous: $e);
                }
                $parameter = $parameter->fromArray($validated);
            }
            $injectParams[] = $parameter;
        }

        return parent::callAction($method, $injectParams);
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

    protected function redirectTo404Error(): RedirectResponse
    {
        return $this->redirector->action(Show404ErrorAction::class);
    }

    protected function redirectToError(): RedirectResponse
    {
        return $this->redirector->action(ShowUnexpectedErrorAction::class);
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
}
