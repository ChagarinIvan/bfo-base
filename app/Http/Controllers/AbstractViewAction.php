<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use \Illuminate\Contracts\View\Factory as ViewFactory;

abstract class AbstractViewAction extends Controller
{
    protected ViewFactory $viewFactory;

    public function __construct(ViewFactory $viewFactory)
    {
        $this->viewFactory = $viewFactory;
    }
}
