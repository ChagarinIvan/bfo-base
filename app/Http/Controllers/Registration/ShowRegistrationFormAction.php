<?php

declare(strict_types=1);

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\AbstractViewAction;
use Illuminate\Contracts\View\View;

class ShowRegistrationFormAction extends AbstractViewAction
{
    public function __invoke(): View
    {
        return $this->viewFactory->make('auth.registration');
    }
}
