<?php

namespace App\View\Components;

use App\Models\Club;
use Illuminate\View\Component;
use Illuminate\View\View;

class ClubLink extends Component
{
    public function __construct(private readonly ?Club $club) {}

    public function render(): View
    {
        return view('components.club-link', ['club' => $this->club]);
    }
}
