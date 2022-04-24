<?php

namespace App\Http\Controllers\Rank;

use App\Models\Rank;
use App\Services\PersonsIdentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Проверка разрядов базы будущих соревнований на основе csv файла
 */
class CheckPersonsRanksAction extends AbstractRankAction
{
    public function __invoke(Request $request): View
    {
        $list = $request->file('list');
        $list = $list->getContent();
        $list = $this->parserService->parserRankList($list);
        $list = $this->preparedLines($list);
        $personsList = $this->identService->identLines($list->keys()->toArray());
        $personsList = Collection::make($personsList);
        $persons = $this->personsService->getPersons($personsList->values())->keyBy('id');
        $ranks = $this->rankService->getActualRanks($personsList->values());

        return $this->view(
            'ranks.check-list',
            compact('list', 'ranks', 'personsList', 'persons')
        );
    }

    public function preparedLines(Collection $lines): Collection
    {
        return $lines->transform(function(array $line): array {
            try {
                $line['name'] = str_replace(' ', ' ', $line['name']);
                [$lastname, $firstname] = preg_split('#\s#', $line['name']);
            } catch (\Exception $e) {
            }
            $line['prepared_line'] = PersonsIdentService::makeIdentLine(
                $lastname,
                $firstname,
                empty($line['year']) ? null : (int)$line['year']
            );

            $line['rank'] = isset($line['rank']) && ! empty($line['rank'])
                ? Rank::getRank($line['rank'])
                : null;

            return $line;
        })
            ->keyBy('prepared_line');
    }
}
