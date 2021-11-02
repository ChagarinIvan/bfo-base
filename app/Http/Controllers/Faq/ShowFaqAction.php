<?php

namespace App\Http\Controllers\Faq;

use App\Http\Controllers\AbstractAction;
use App\Models\Club;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShowFaqAction extends AbstractAction
{
    public function __invoke(Request $request, Club $club): View|RedirectResponse
    {
       return $this->view('faq.index');
    }

    protected function isFaqRoute(): bool
    {
        return true;
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
