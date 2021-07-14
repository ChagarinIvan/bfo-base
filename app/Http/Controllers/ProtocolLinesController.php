<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\IdentLine;
use App\Models\Person;
use App\Models\PersonPrompt;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
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
        $personsQuery = Person::with('club')->orderBy('lastname');

        if(strlen($search) > 0) {
            $personsQuery->where('firstname', 'LIKE', '%'.$search.'%')
                ->orWhere('lastname', 'LIKE', '%'.$search.'%')
                ->orWhere('birthday', 'LIKE', '%'.$search.'%');
        }

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

        //создаём новый промпт, т.к. видно простая идентификаия не помогла
        $prompt = new PersonPrompt();
        $prompt->person_id = $personId;
        $prompt->prompt = $protocolLine->prepared_line;
        $prompt->save();

        //закідываем на новую проверку все не идентифицированные строки протоколов
        //т.к. с новым промтом мы может найти новые совпадения
        $preparedLines = ProtocolLine::whereNull('person_id')
            ->get()
            ->pluck('prepared_line')
            ->unique();

        $identLines = IdentLine::whereIn('ident_line', $preparedLines)
            ->get()
            ->pluck('ident_line');

        $preparedLines = $preparedLines->diff($identLines);

        (new IdentService())->pushIdentLines($preparedLines);

        //сохраняем результат для всех строчек с установленным идетификатором
        $protocolLinesToRecheck = ProtocolLine::wherePreparedLine($protocolLine->prepared_line)->get();

        foreach ($protocolLinesToRecheck as $protocolLine) {
            $protocolLine->person_id = $personId;
            $protocolLine->save();
        }

        $url = Session::get('prev_url');
        Session::forget('prev_url');

        return redirect($url);
    }
}
