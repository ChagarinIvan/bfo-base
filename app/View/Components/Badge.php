<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Badge extends Component
{
    public string $color;
    public string $name;
    public string $url;

    public function __construct(
        string $color = '',
        string $name = '',
        string $url = '',
    ) {
        $this->color = $color;
        $this->name = $name;
        $this->url = $url;
    }

    public function render(): View
    {
        return view('components.badge');
    }
}
