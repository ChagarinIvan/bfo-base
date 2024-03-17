<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Bridge\Laravel\Http\Controllers\Action;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowCreatePromptAction extends BaseController
{
    use Action;

    public function __invoke(string $personId): View
    {
        /** @see /resources/views/person-prompt/create.blade.php */
        return $this->view('person-prompt.create', compact('personId'));
    }
}
