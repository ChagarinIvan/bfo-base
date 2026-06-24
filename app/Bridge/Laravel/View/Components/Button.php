<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Button extends Component
{
    public string $text;

    public function __construct(
        string $text = '',
        public string $color = '',
        public string $icon = '',
        public string $url = '',
    ) {
        $this->text = __($text);
    }

    public function render(): View
    {
        return view('components.button');
    }
}
