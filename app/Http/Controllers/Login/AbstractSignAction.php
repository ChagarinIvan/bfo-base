<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\AbstractAction;
use App\Services\BackUrlService;
use App\Services\ViewActionsService;
use Illuminate\Auth\AuthManager;
use Illuminate\Routing\Redirector;

abstract class AbstractSignAction extends AbstractAction
{
    protected AuthManager $authManager;

    public function __construct(
        ViewActionsService $viewService,
        Redirector $redirector,
        BackUrlService $backUrlService,
        AuthManager $sessionGuard
    ) {
        parent::__construct($viewService, $redirector, $backUrlService);
        $this->authManager = $sessionGuard;
    }
}
