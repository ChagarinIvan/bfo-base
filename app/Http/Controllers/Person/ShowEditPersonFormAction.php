<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Club;
use App\Models\Person;
use App\Services\UserService;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;

class ShowEditPersonFormAction extends AbstractPersonViewAction
{
    private UrlGenerator $urlGenerator;

    public function __construct(ViewFactory $viewFactory, UserService $userService, UrlGenerator $urlGenerator)
    {
        parent::__construct($viewFactory, $userService);
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(Person $person): View
    {
        $clubs = Club::orderBy('name')->get();
        return $this->view('persons.edit', [
            'person' => $person,
            'clubs' => $clubs,
            'redirect' => $this->urlGenerator->previous(),
        ]);
    }
}
