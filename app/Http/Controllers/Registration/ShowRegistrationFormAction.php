<?php

declare(strict_types=1);

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;

class ShowRegistrationFormAction extends AbstractAction
{
    public function __invoke(): View
    {
        return $this->view('auth.registration');
    }
}
