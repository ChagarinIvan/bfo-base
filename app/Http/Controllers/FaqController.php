<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class FaqController
{
    public function index(): View
    {
        return view('faq.index');
    }

    public function api(): View
    {
        return view('faq.api');
    }
}
