<?php

namespace App\Http\Controllers\Person;

use App\Models\Club;
use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEditPersonFormAction extends AbstractPersonAction
{
    public function __invoke(Person $person): View|RedirectResponse
    {
        $clubs = Club::orderBy('name')->get();

        return $this->view('persons.edit', [
            'person' => $person,
            'clubs' => $clubs,
        ]);
    }
}
