<?php

namespace App\Http\Controllers;

use App\Services\BackUrlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;

class BackAction extends Controller
{
    public function __construct(protected Redirector $redirector, protected BackUrlService $backUrlService)
    {}

    public function __invoke(): RedirectResponse
    {
        $url = $this->backUrlService->pop();
        return $this->redirector->to($url);
    }
}
