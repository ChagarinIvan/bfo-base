<?php

declare(strict_types=1);

namespace App\Http\Controllers\Error;

use App\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;

class Show404ErrorAction extends AbstractAction
{
    public function __invoke(): View
    {
        return $this->view('errors.404');
    }
}
