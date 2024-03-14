<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Login;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use App\Services\ViewActionsService;
use Illuminate\Auth\AuthManager;
use Illuminate\Routing\Redirector;

abstract class AbstractSignAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewService,
        protected Redirector $redirector,
        protected AuthManager $sessionGuard
    ) {
        parent::__construct($viewService, $redirector);
    }
}
