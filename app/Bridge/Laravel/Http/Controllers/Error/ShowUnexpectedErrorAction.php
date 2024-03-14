<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Error;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowUnexpectedErrorAction extends AbstractAction
{
    public function __invoke(): View|RedirectResponse
    {
        return $this->view('errors.error');
    }
}
