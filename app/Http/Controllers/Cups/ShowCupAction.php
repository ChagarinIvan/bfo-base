<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use Illuminate\Contracts\View\View;

class ShowCupAction extends AbstractCupViewAction
{
    public function __invoke(Cup $cup): View
    {
        $events = $cup->events()->with('event')->get()->sortBy('events.date');

        return $this->view('cup.show', [
            'cup' => $cup,
            'events' => $events,
        ]);
    }
}
