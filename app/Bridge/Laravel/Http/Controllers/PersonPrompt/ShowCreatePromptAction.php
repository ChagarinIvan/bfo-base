<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\PersonPrompt;

use App\Bridge\Laravel\Http\Controllers\Person\PersonAction;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

class ShowCreatePromptAction extends BaseController
{
    use PersonAction;

    public function __invoke(string $personId): View
    {
        /** @see /resources/views/person-prompt/create.blade.php */
        return $this->view('person-prompt.create', ['personId' => $personId]);
    }
}
