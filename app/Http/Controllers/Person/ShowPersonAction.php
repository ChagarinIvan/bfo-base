<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\PersonPayment;
use App\Models\ProtocolLine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class ShowPersonAction extends AbstractPersonAction
{
    public function __invoke(int $personId): View|RedirectResponse
    {
        $person = $this->personsService->getPerson($personId);
        $payments = $person->payments->sortByDesc(static fn (PersonPayment $payment) => $payment->date);
        $groupedProtocolLines = $person->protocolLines->groupBy(static fn (ProtocolLine $line) => $line->distance->event->date->format('Y'));
        $groupedProtocolLines->transform(static function (Collection $protocolLines) {
            return $protocolLines->sortByDesc(static fn (ProtocolLine $line) => $line->distance->event->date);
        });
        $groupedProtocolLines = $groupedProtocolLines->sortKeysDesc();

        return $this->view('persons.show', [
            'person' => $person,
            'groupedProtocolLines' => $groupedProtocolLines,
            'rank' => $this->rankService->getActiveRank($person->id),
            'personPayment' => $payments->first(),
        ]);
    }
}
