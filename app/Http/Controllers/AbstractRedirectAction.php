<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BackUrlService;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;

abstract class AbstractRedirectAction extends Controller
{
    protected Redirector $redirector;
    protected BackUrlService $backUrlService;

    public function __construct(Redirector $redirector, BackUrlService $backUrlService)
    {
        $this->redirector = $redirector;
        $this->backUrlService = $backUrlService;
    }

    protected function removeLastBackUrl(): void
    {
        $this->backUrlService->pop();
    }
}
