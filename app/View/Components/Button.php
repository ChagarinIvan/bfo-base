<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Button extends Component
{
    public string $text;
    public string $color;
    public string $icon;
    public string $url;

    public function __construct(
        string $text = '',
        string $color = '',
        string $icon = '',
        string $url = '',
    ) {
        $this->text = __($text);
        $this->color = $color;
        $this->icon = $icon;
        $this->url = $url;
    }

    public function render(): View
    {
        return view('components.button');
    }
}
