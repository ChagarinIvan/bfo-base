<?php

namespace App\Http\Controllers\Faq;

use App\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowApiFaqAction extends AbstractAction
{
    public function __invoke(): View|RedirectResponse
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
