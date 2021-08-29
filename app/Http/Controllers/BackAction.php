<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BackUrlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;

class BackAction extends Controller
{
    protected Redirector $redirector;
    private BackUrlService $backUrlService;

    public function __construct(Redirector $redirector, BackUrlService $backUrlService)
    {
        $this->redirector = $redirector;
        $this->backUrlService = $backUrlService;
    }

    public function __invoke(): RedirectResponse
    {
        $url = $this->backUrlService->pop();
        return $this->redirector->to($url);
    }
}
