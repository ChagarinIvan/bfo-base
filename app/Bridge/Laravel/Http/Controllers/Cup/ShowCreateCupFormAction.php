<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowCreateCupFormAction extends BaseController
{
    use CupAction;

    public function __invoke(): View
    {
        /** @see /resources/views/cup/create.blade.php */
        return $this->view('cup.create');
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
