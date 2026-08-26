<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Error;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;

class ShowUnexpectedErrorAction extends AbstractAction
{
    public function __invoke(): View
    {
        /** @see /resources/views/errors/error.blade.php */
        return $this->view('errors.error');
    }
}
