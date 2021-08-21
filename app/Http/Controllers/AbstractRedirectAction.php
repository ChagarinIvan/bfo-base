<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;

abstract class AbstractRedirectAction extends Controller
{
    protected Redirector $redirector;

    public function __construct(Redirector $redirector)
    {
        $this->redirector = $redirector;
    }
}
