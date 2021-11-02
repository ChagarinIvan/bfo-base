<?php

namespace App\Http\Controllers\Flags;

use App\Http\Controllers\AbstractAction;
use App\Models\Flag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreFlagAction extends AbstractAction
{
    public function __invoke(Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'color' => 'required',
        ]);

        $flag = new Flag($formParams);
        $flag->save();

        return $this->redirector->action(ShowFlagsListAction::class);
    }
}
