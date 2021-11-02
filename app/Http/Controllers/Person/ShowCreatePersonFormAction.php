<?php

namespace App\Http\Controllers\Person;

use App\Models\Club;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCreatePersonFormAction extends AbstractPersonAction
{
    public function __invoke(): View|RedirectResponse
    {
        $clubs = Club::orderBy('name')->get();
        return $this->view('persons.create', ['clubs' => $clubs]);
    }
}
