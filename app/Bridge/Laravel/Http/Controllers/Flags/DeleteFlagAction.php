<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Flags;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use App\Models\Flag;
use Illuminate\Http\RedirectResponse;

class DeleteFlagAction extends AbstractAction
{
    public function __invoke(Flag $flag): RedirectResponse
    {
        $flag->delete();
        return $this->redirector->action(ShowFlagsListAction::class);
    }
}
