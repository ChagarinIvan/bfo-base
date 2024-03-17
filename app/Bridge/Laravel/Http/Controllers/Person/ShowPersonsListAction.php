<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

class ShowPersonsListAction extends BaseController
{
    use PersonAction;

    public function __invoke(): View
    {
        /** @see /resources/views/persons/index.blade.php */
        return $this->view('persons.index');
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
