<?php
declare(strict_types=1);

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Modal extends Component
{
    public string $modalId;
    public Button $button;
    public string $content;
    public string $header;

    public function __construct(
        string $modalId,
        string $url = '#',
        string $header = 'app.common.warn',
        string $content = 'app.common.delete_warn',
        string $text = 'app.common.delete',
        string $color = 'danger',
        string $icon = 'bi-trash-fill',
    ) {
        $this->button = new Button($text, $color, $icon, $url);
        $this->modalId = $modalId;
        $this->header = __($header);
        $this->content = __($content);
    }

    public function render(): View
    {
        return view('components.modal');
    }
}
