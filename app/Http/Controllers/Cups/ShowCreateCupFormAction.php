<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use Illuminate\Contracts\View\View;

class ShowCreateCupFormAction extends AbstractCupAction
{
    public function __invoke(int $year): View
    {
        return $this->view('cup.create', [
            'groups' => $this->groupsRepository->getAll(),
            'selectedYear' => $year,
        ]);
    }
}
