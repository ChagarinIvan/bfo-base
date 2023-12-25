<?php

declare(strict_types=1);

namespace App\Http\Controllers\CupEvents;

use App\Http\Controllers\Cups\AbstractCupAction;
use App\Http\Controllers\Cups\ShowCupAction;
use App\Models\Cup;
use App\Models\CupEvent;
use Illuminate\Http\RedirectResponse;

class DeleteCupEventAction extends AbstractCupAction
{
    public function __invoke(Cup $cup, CupEvent $event): RedirectResponse
    {
        $event->delete();
        return $this->redirector->action(ShowCupAction::class, [$cup]);
    }
}
