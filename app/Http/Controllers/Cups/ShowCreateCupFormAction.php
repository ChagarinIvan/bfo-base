<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCreateCupFormAction extends AbstractCupAction
{
    public function __invoke(string $year): View|RedirectResponse
    {
        return $this->view('cup.create', ['selectedYear' => $year,]);
    }
}
