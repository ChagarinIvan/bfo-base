<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowRegistrationFormAction extends AbstractAction
{
    public function __invoke(): View|RedirectResponse
    {
        return $this->view('auth.registration');
    }
}
