<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Http\Controllers\AbstractRedirectAction;
use App\Models\Cup;
use App\Models\Cups\CupType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateCupAction extends AbstractRedirectAction
{
    public function __invoke(Cup $cup, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required|unique:competitions|max:255',
            'year' => 'required|digits:4',
            'type' => 'required',
            'groups' => 'required|array',
            'events_count' => 'required|numeric',
            'groups.*' => 'integer',
        ]);

        $cup->name = $formParams['name'];
        $cup->year = $formParams['year'];
        $cup->type = $formParams['type'];
        if (!in_array($cup->type, array_keys(CupType::CLASS_MAP), true)) {
            $cup->type = CupType::ELITE;
        }
        $cup->events_count = $formParams['events_count'];
        $cup->save();
        $cup->groups()->sync($formParams['groups']);

        return $this->redirector->action(ShowCupAction::class, [$cup]);
    }
}
