<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\View\Components;

use App\Bridge\Laravel\Http\Controllers\BackAction;

final class BackButton extends Button
{
    public function __construct()
    {
        parent::__construct('app.common.back', 'danger', 'bi-reply-fill', action(BackAction::class));
    }
}
