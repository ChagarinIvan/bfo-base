<?php
declare(strict_types=1);

namespace App\View\Components;

use App\Facades\Color;
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
        $this->color = empty($color) ? Color::getColor($name) : $color;
        $this->name = $name;
        $this->url = $url;
    }

    public function render(): View
    {
        return view('components.badge');
    }
}
