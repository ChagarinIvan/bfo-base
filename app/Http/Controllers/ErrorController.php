<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ErrorController
{
    public function action404(): View
    {
        return view('errors.404');
    }
}
