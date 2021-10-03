<?php

declare(strict_types=1);

namespace App\Http\Controllers\Login;

use App\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;

class ShowLoginFormAction extends AbstractAction
{
    public function __invoke(): View
    {
        return $this->view('auth.login');
    }
}
