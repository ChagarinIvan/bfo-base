<?php

declare(strict_types=1);

namespace App\Http\Controllers\Faq;

use App\Http\Controllers\AbstractViewAction;
use App\Models\Club;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowApiFaqAction extends AbstractViewAction
{
    public function __invoke(Request $request, Club $club): View
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
