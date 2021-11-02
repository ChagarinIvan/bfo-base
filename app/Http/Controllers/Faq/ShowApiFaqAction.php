<?php

namespace App\Http\Controllers\Faq;

use App\Http\Controllers\AbstractAction;
use App\Models\Club;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShowApiFaqAction extends AbstractAction
{
    public function __invoke(Request $request, Club $club): View|RedirectResponse
    {
       return $this->view('faq.api');
    }

    protected function isFaqApiRoute(): bool
    {
        return true;
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
