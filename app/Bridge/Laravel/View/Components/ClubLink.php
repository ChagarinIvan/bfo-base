<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\View\Components;

use App\Models\Club;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class ClubLink extends Component
{
    public function __construct(public readonly ?Club $club)
    {
    }

    public function render(): View
    {
        return view('components.club-link', ['club' => $this->club]);
    }
}
