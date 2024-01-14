<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShowSetPersonToProtocolLineAction extends AbstractPersonAction
{
    public function __invoke(string $protocolLineId, Request $request): View|RedirectResponse
    {
        /** @var ProtocolLine $protocolLine */
        $protocolLine = ProtocolLine::find($protocolLineId);
        $persons = Person::with('club')->get();

        return $this->view('protocol-line.edit-person', [
            'protocolLine' => $protocolLine,
            'persons' => $persons,
        ]);
    }
}
