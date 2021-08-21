<?php

declare(strict_types=1);

namespace App\Http\Controllers\Login;

use App\Http\Controllers\AbstractViewAction;
use Illuminate\Contracts\View\View;

class ShowLoginFormAction extends AbstractViewAction
{
    public function __invoke(): View
    {
        return $this->viewFactory->make('auth.login');
    }
}
