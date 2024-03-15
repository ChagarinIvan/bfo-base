<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Error;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class Show404ErrorAction extends AbstractAction
{
    public function __invoke(): View
    {
        return $this->view('errors.404error');
    }
}
