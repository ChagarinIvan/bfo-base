<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;

final class ModalButton extends Button
{
    public function __construct(
        public readonly string $modalId,
        public string $text = 'app.common.delete',
        public string $color = 'danger',
        public string $icon = 'bi-trash-fill',
    ) {
        parent::__construct($text, $color, $icon);
    }

    public function render(): View
    {
        return view('components.modal-button');
    }
}
