<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Models\Club;
use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowClubAction extends AbstractClubAction
{
    public function __invoke(Request $request, Club $club): View
    {
        $persons = Person::with(['protocolLines', 'club'])
            ->orderBy('lastname')
            ->where('club_id', $club->id)
            ->get();

        return $this->view('clubs.show', ['club' => $club, 'persons' => $persons,]);
    }
}
