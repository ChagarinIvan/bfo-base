<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowSetPersonToProtocolLineAction extends BaseController
{
    use PersonAction;

    public function __invoke(string $protocolLineId, Request $request): View
    {
        /** @var ProtocolLine $protocolLine */
        $protocolLine = ProtocolLine::find($protocolLineId);
        $persons = Person::where('active', true)->with('club')->get();

        /** @see /resources/views/protocol-line/edit-person.blade.php */
        return $this->view('protocol-line.edit-person', compact('protocolLine', 'persons'));
    }
}
