<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Models\Club;
use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowClubAction extends AbstractClubViewAction
{
    public function __invoke(Request $request, Club $club): View
    {
        $search = (string)$request->get('search');

        $personsQuery = Person::with(['protocolLines', 'club'])->orderBy('lastname');
        if(strlen($search) > 0) {
            $personsQuery->where(function ($query) use ($search) {
                $query->where('firstname', 'LIKE', '%'.$search.'%')
                    ->orWhere('lastname', 'LIKE', '%'.$search.'%');
            });
        }
        $persons = $personsQuery->where('club_id', $club->id)->paginate(13);

        return $this->view('clubs.show', ['club' => $club, 'persons' => $persons, 'search' => $search,]);
    }
}
