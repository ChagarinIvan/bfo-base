<?php

namespace App\Http\Controllers\PersonPrompt;

use App\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;

class ShowEditPromptAction extends AbstractAction
{
    public function __invoke(int $personId, int $promptId): View
    {
        return $this->view('person-prompt.create', compact('personId'));
    }
}
