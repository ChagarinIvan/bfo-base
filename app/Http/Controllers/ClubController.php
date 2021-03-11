<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ClubController extends Controller
{
    public function index(Request $request): View
    {
        $search = (string)$request->get('search');
        $clubsQuery = Club::orderBy('name');
        if(strlen($search) > 0) {
            $clubsQuery->where('name', 'LIKE', '%'.$search.'%');
        }
        $clubs = $clubsQuery->paginate(20);
        return view('clubs.index', ['clubs' => $clubs, 'search' => $search]);
    }

    public function show(Request $request, int $clubId): View
    {
        $search = (string)$request->get('search');
        $club = Club::find($clubId);

        $personsQuery = Person::with(['protocolLines', 'club'])->orderBy('lastname');
        if(strlen($search) > 0) {
            $personsQuery->where(function($query) use ($search) {
                $query->where('firstname', 'LIKE', '%'.$search.'%')
                    ->orWhere('lastname', 'LIKE', '%'.$search.'%');
            });
        }
        $persons = $personsQuery->where('club_id', $clubId)->paginate(13);

        return view('clubs.show', ['club' => $club, 'persons' => $persons, 'search' => $search]);
    }
}
