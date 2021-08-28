<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use Illuminate\Contracts\View\View;

class ShowCupsListAction extends AbstractCupViewAction
{
    public function __invoke(int $year): View
    {
        $cups = Cup::where('year', $year)->get();

        return $this->view('cup.index', [
            'cups' => $cups,
            'selectedYear' => $year,
        ]);
    }
}
