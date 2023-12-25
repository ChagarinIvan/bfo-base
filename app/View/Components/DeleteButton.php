<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;

final class DeleteButton extends Button
{
    public function __construct(
        public readonly string $modalId,
    ) {
        parent::__construct('app.common.delete', 'danger', 'bi-trash-fill');
    }

    public function render(): View
    {
        return view('components.delete-button');
    }
}
