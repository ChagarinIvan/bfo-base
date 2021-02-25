<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class ClubController extends Controller
{

    public function index(): View
    {
        $clubs = Club::orderBy('name')->paginate(10);
        return view('clubs.index', ['clubs' => $clubs]);
    }

    public function show(int $clubId): View
    {
        $club = Club::find($clubId);
        $persons = Person::with('protocolLines')->orderBy('lastname')->whereClubId($clubId)->paginate(10);

        return view('clubs.show', ['club' => $club, 'persons' => $persons,]);
    }
}
