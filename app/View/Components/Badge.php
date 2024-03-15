<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Bridge\Laravel\Facades\Color;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Badge extends Component
{
    public readonly string $color;

    public function __construct(
        string $color = '',
        public readonly string $name = '',
        public readonly string $url = '',
    ) {
        $this->color = empty($color) ? Color::getColor($name) : $color;
    }

    public function render(): View
    {
        return view('components.badge');
    }
}
