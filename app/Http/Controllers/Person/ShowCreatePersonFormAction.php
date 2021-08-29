<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractViewAction;
use App\Models\Club;
use Illuminate\Contracts\View\View;

class ShowCreatePersonFormAction extends AbstractPersonViewAction
{
    public function __invoke(): View
    {
        $clubs = Club::orderBy('name')->get();
        return $this->view('persons.create', ['clubs' => $clubs]);
    }
}
