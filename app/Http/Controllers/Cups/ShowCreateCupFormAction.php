<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Group;
use Illuminate\Contracts\View\View;

class ShowCreateCupFormAction extends AbstractCupViewAction
{
    public function __invoke(int $year): View
    {
        return $this->view('cup.create', [
            'groups' => Group::all(),
            'selectedYear' => $year,
        ]);
    }
}
