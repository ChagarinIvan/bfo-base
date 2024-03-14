<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Login;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowLoginFormAction extends AbstractAction
{
    public function __invoke(): View|RedirectResponse
    {
        return $this->view('auth.login');
    }
}
