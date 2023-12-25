<?php

declare(strict_types=1);

namespace App\Http\Controllers\PersonPrompt;

use App\Http\Controllers\AbstractAction;
use Illuminate\Contracts\View\View;
use function compact;

class ShowCreatePromptAction extends AbstractAction
{
    public function __invoke(int $personId): View
    {
        return $this->view('person-prompt.create', compact('personId'));
    }
}
