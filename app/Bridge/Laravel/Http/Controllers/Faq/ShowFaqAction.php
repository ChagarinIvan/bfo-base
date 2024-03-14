<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Faq;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowFaqAction extends AbstractAction
{
    public function __invoke(): View|RedirectResponse
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
