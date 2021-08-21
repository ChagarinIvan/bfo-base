<?php

declare(strict_types=1);

namespace App\Http\Controllers\Error;

use App\Http\Controllers\AbstractViewAction;
use Illuminate\Contracts\View\View;

class Show404ErrorAction extends AbstractViewAction
{
    public function __invoke(): View
    {
        return $this->viewFactory->make('errors.404');
    }
}
