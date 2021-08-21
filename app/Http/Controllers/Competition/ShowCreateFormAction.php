<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\AbstractViewAction;
use Illuminate\Contracts\View\View;

class ShowCreateFormAction extends AbstractViewAction
{
    public function __invoke(int $year): View
    {
        return $this->viewFactory->make('competitions.create', ['year' => $year,]);
    }
}
