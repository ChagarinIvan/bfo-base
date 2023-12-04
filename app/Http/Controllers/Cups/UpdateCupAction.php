<?php

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use App\Models\Cups\CupType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateCupAction extends AbstractCupAction
{
    public function __invoke(Cup $cup, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required|unique:competitions|max:255',
            'year' => 'required|digits:4',
            'type' => 'required',
            'events_count' => 'required|numeric',
        ]);

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
