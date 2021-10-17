<?php

namespace App\View\Components;

use Illuminate\View\View;

class DeleteButton extends Button
{
    public string $modalId;

    public function __construct(
        string $modalId,
    ) {
        parent::__construct('app.common.delete', 'danger', 'bi-trash-fill');
        $this->modalId = $modalId;
    }

    public function render(): View
    {
        return view('components.delete-button');
    }
}
