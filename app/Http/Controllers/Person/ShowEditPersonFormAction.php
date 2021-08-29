<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Club;
use App\Models\Person;
use Illuminate\Contracts\View\View;

class ShowEditPersonFormAction extends AbstractPersonViewAction
{
    public function __invoke(Person $person): View
    {
        $clubs = Club::orderBy('name')->get();

        return $this->view('persons.edit', [
            'person' => $person,
            'clubs' => $clubs,
        ]);
    }
}
