<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractRedirectAction;
use App\Models\Person;
use App\Models\PersonPrompt;
use App\Models\ProtocolLine;
use App\Services\BackUrlService;
use App\Services\RankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class SetProtocolLinePersonAction extends AbstractRedirectAction
{
    private RankService $rankService;

    public function __construct(Redirector $redirector, BackUrlService $backUrlService, RankService $rankService)
    {
        parent::__construct($redirector, $backUrlService);
        $this->rankService = $rankService;
    }

    public function __invoke(Person $person, int $protocolLineId): RedirectResponse
    {
        /** @var ProtocolLine $protocolLine */
        $protocolLine = ProtocolLine::find($protocolLineId);
        $oldPersonId = $protocolLine->person_id;
        $preparedLine = $protocolLine->prepared_line;

        //сохраняем результат для всех строчек с установленным идентификатором
        $protocolLinesToUpdate = ProtocolLine::wherePreparedLine($preparedLine)->get();

        foreach ($protocolLinesToUpdate as $protocolLine) {
            //перекидываем разряд
            $rank = $this->rankService->getRank($protocolLine);
            if ($rank !== null) {
                $rank->person_id = $person->id;
                $rank->save();
            }

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

        return $this->redirector->to($this->backUrlService->pop());
    }
}
