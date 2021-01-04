<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\View;

class PersonController extends BaseController
{
    public function index(): View
    {
        $persons = Person::all();
        return view('persons.index', ['persons' => $persons]);
    }
}
