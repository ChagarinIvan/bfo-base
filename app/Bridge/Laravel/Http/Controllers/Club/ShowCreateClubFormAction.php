<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Club;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

class ShowCreateClubFormAction extends BaseController
{
    use ClubAction;

    public function __invoke(): View
    {
        /** @see /resources/views/clubs/create.blade.php */
        return $this->view('clubs.create');
    }
}
