<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Dto\Person\PersonAssembler;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Services\PersonsService;
use App\Services\RankService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;

class ShowPersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        string $personId,
        PersonsService $personsService,
        RankService $rankService,
        PersonAssembler $assembler,
    ): View {
        $person = $personsService->getPerson((int) $personId);
        $payments = $person->payments->sortByDesc(static fn (PersonPayment $payment) => $payment->date);
        $groupedProtocolLines = $person->protocolLines->groupBy(static fn (ProtocolLine $line) => $line->distance->event->date->format('Y'));
        $groupedProtocolLines->transform(static function (Collection $protocolLines) {
            return $protocolLines->sortByDesc(static fn (ProtocolLine $line) => $line->distance->event->date);
        });
        $groupedProtocolLines = $groupedProtocolLines->sortKeysDesc();

        /** @see /resources/views/persons/show.blade.php */
        return $this->view('persons.show', [
            'person' => $assembler->toViewPersonDto($person),
            'groupedProtocolLines' => $groupedProtocolLines,
            'rank' => $rankService->getActiveRank($person->id),
            'personPayment' => $payments->first(),
        ]);
    }
}
