<?php

namespace App\View\Components;

use App\Http\Controllers\BackAction;

class BackButton extends Button
{
    public function __construct() {
        parent::__construct('app.common.back', 'danger', 'bi-reply-fill', action(BackAction::class));
    }
}