<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Club;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class ClubController extends Controller
{

    public function index(): View
    {
        $clubs = Club::all();
        $clubs = $clubs->sortBy('name');
        return view('clubs.index', ['clubs' => $clubs]);
    }

    public function show(int $clubId): View
    {
        $club = Club::with('persons.protocolLines')->find($clubId);
        return view('persons.index', [
            'title' => __('app.club.name').' '.$club->name,
            'persons' => $club->persons->sortBy('lastname'),
        ]);
    }
}
