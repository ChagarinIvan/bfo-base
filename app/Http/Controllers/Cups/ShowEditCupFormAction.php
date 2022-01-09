<?php

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEditCupFormAction extends AbstractCupAction
{
    public function __invoke(Cup $cup): View|RedirectResponse
    {
        return $this->view('cup.edit', [
            'cup' => $cup,
        ]);
    }
}
