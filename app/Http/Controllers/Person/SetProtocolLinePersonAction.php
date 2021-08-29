<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractRedirectAction;
use App\Models\Person;
use App\Models\PersonPrompt;
use App\Models\ProtocolLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class SetProtocolLinePersonAction extends AbstractRedirectAction
{
    public function __invoke(ProtocolLine $protocolLine, Person $person): RedirectResponse
    {
        $oldPersonId = $protocolLine->person_id;
        $preparedLine = $protocolLine->prepared_line;

        //сохраняем результат для всех строчек с установленным идентификатором
        $protocolLinesToUpdate = ProtocolLine::wherePreparedLine($preparedLine)->get();

        foreach ($protocolLinesToUpdate as $protocolLine) {
            $protocolLine->person_id = $person->id;
            $protocolLine->save();
        }

        //меняем person_id для имеющихся таких же идентификаторов
        $prompts = PersonPrompt::wherePrompt($preparedLine)->get();
        if ($prompts->count() > 0) {
            foreach ($prompts as $prompt) {
                $prompt->person_id = $person->id;
                $prompt->save();
            }
        } else {
            //создаём новый промпт
            $prompt = new PersonPrompt();
            $prompt->person_id = $person->id;
            $prompt->prompt = $preparedLine;
        }

        if (ProtocolLine::wherePersonId($oldPersonId)->count() === 0) {
            Person::destroy($oldPersonId);
        };

        $url = Session::get('prev_url');
        Session::forget('prev_url');

        return $this->redirector->to($url);
    }
}
