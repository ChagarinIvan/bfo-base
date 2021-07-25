<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\IdentLine;
use App\Models\Person;
use App\Models\PersonPrompt;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProtocolLinesController extends BaseController
{
    public function editPerson(Request $request, int $protocolLineId): View
    {
        if (!Session::exists('prev_url')) {
            Session::put('prev_url', url()->previous());
        }
        $search = (string)$request->get('search');
        $protocolLine = ProtocolLine::find($protocolLineId);
        $personsQuery = Person::with('club');

        if(strlen($search) > 0) {
            $personsQuery->where('firstname', 'LIKE', '%'.$search.'%')
                ->orWhere('lastname', 'LIKE', '%'.$search.'%')
                ->orWhere('birthday', 'LIKE', '%'.$search.'%');
        }

        $orderCase = "CASE
                    WHEN lastname LIKE '%{$protocolLine->lastname}%' THEN 0
                    WHEN firstname LIKE '%{$protocolLine->firstname}%' THEN 1
                    WHEN birthday LIKE '%{$protocolLine->year}%' THEN 3
                 ";

        $clubs = Club::whereName($protocolLine->club)->get();

        if ($clubs->count() > 0) {
            /** @var Club $club */
            $club = $clubs->first();
            $orderCase .= " WHEN club_id = {$club->id} THEN 2";
        }
        $orderCase .= ' ELSE 3 END ASC';
        $personsQuery->orderByRaw(DB::raw($orderCase));
        $persons = $personsQuery->paginate(13);

        return view('protocol-line.edit-person', [
            'protocolLine' => $protocolLine,
            'persons' => $persons,
            'search' => $search,
        ]);
    }

    /**
     * теперь при установке персон ИД надо смотреть если у старого персон ИД только одна запись, то надо этот старый персону далить
     *
     * @param int $protocolLineId
     * @param int $personId
     * @return RedirectResponse
     */
    public function setPerson(int $protocolLineId, int $personId): RedirectResponse
    {
        $protocolLine = ProtocolLine::find($protocolLineId);
        $oldPersonId = $protocolLine->person_id;
        $preparedLine = $protocolLine->prepared_line;

        //сохраняем результат для всех строчек с установленным идетификатором
        $protocolLinesToUpdate = ProtocolLine::wherePreparedLine($preparedLine)->get();

        foreach ($protocolLinesToUpdate as $protocolLine) {
            $protocolLine->person_id = $personId;
            $protocolLine->save();
        }

        //меняем person_id для имеющися таких же идентификаторов
        $prompts = PersonPrompt::wherePrompt($preparedLine)->get();
        if ($prompts->count() > 0) {
            foreach ($prompts as $prompt) {
                $prompt->person_id = $personId;
                $prompt->save();
            }
        } else {
            //создаём новый промпт
            $prompt = new PersonPrompt();
            $prompt->person_id = $personId;
            $prompt->prompt = $preparedLine;
        }

        if (ProtocolLine::wherePersonId($oldPersonId)->count() === 0) {
            Person::destroy($oldPersonId);
        };

        $url = Session::get('prev_url');
        Session::forget('prev_url');

        return redirect($url);
    }
}
