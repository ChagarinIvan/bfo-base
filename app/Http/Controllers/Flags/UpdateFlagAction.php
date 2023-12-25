<?php
declare(strict_types=1);

namespace App\Http\Controllers\Flags;

use App\Http\Controllers\AbstractAction;
use App\Models\Flag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdateFlagAction extends AbstractAction
{
    public function __invoke(Flag $flag, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required',
            'color' => 'required',
        ]);

        $flag->fill($formParams);
        $flag->save();

        return $this->redirector->action(ShowFlagsListAction::class);
    }
}
