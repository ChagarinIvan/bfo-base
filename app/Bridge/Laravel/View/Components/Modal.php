<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Modal extends Component
{
    public readonly Button $button;
    public readonly string $content;
    public readonly string $header;

    public function __construct(
        public readonly string $modalId,
        string $url = '#',
        string $header = 'app.common.warn',
        string $content = 'app.common.delete_warn',
        string $text = 'app.common.delete',
        string $color = 'danger',
        string $icon = 'bi-trash-fill',
    ) {
        $this->button = new Button($text, $color, $icon, $url);
        $this->header = __($header);
        $this->content = __($content);
    }

    public function render(): View
    {
        return view('components.modal');
    }
}
