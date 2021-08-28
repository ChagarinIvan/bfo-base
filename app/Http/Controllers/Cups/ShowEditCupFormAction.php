<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use App\Models\Group;
use Illuminate\Contracts\View\View;

class ShowEditCupFormAction extends AbstractCupViewAction
{
    public function __invoke(Cup $cup): View
    {
        return $this->view('cup.edit', [
            'cup' => $cup,
            'groups' => Group::all(),
        ]);
    }
}
