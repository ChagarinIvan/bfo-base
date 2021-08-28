<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Models\Club;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowClubsListAction extends AbstractClubViewAction
{
    public function __invoke(Request $request): View
    {
        $search = (string)$request->get('search');
        $clubsQuery = Club::orderBy('name');
        if(strlen($search) > 0) {
            $clubsQuery->where('name', 'LIKE', '%'.$search.'%');
        }
        $clubs = $clubsQuery->paginate(20);
        return $this->view('clubs.index', ['clubs' => $clubs, 'search' => $search,]);
    }
}
