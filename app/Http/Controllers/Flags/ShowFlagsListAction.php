<?php
declare(strict_types=1);

namespace App\Http\Controllers\Flags;

use App\Models\Flag;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowFlagsListAction extends AbstractFlagsAction
{
    public function __invoke(): View|RedirectResponse
    {
        return $this->view('flags.index', ['flags' => Flag::all()]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
