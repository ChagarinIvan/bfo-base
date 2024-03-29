<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cups;

use App\Models\Cup;
use App\Models\Cups\CupType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use function array_key_exists;

class StoreCupAction extends AbstractCupAction
{
    public function __invoke(Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required|unique:competitions|max:255',
            'year' => 'required|digits:4',
            'type' => 'required',
            'events_count' => 'required|numeric',
            'visible' => 'in:on',
        ]);

        $cup = new Cup();
        $cup->name = $formParams['name'];
        $cup->year = $formParams['year'];
        $cup->type = $formParams['type'];
        $cup->events_count = $formParams['events_count'];
        $cup->visible = ($formParams['visible'] ?? 'off') === 'on';

        if (!array_key_exists($cup->type, CupType::CLASS_MAP)) {
            $cup->type = CupType::ELITE;
        }

        $cup->save();

        return $this->redirector->action(ShowCupAction::class, [$cup]);
    }
}
