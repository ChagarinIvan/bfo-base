<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Club;
use Illuminate\Contracts\View\View;

class ShowCreatePersonFormAction extends AbstractPersonAction
{
    public function __invoke(): View
    {
        $clubs = Club::orderBy('name')->get();
        return $this->view('persons.create', ['clubs' => $clubs]);
    }
}
