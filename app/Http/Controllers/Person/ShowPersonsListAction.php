<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowPersonsListAction extends AbstractPersonAction
{
    public function __invoke(): View|RedirectResponse
    {
        return $this->view('persons.index');
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
