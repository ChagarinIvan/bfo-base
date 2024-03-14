<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;
use function compact;

class ShowCreatePromptAction extends AbstractAction
{
    public function __invoke(string $personId): View
    {
        return $this->view('person-prompt.create', compact('personId'));
    }
}
