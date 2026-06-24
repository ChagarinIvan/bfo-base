<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider;

use App\Bridge\Laravel\View\Components\Badge;
use App\Bridge\Laravel\View\Components\Button;
use App\Bridge\Laravel\View\Components\ClubLink;
use App\Bridge\Laravel\View\Components\EditButton;
use App\Bridge\Laravel\View\Components\Impression;
use App\Bridge\Laravel\View\Components\Modal;
use App\Bridge\Laravel\View\Components\ModalButton;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class ViewProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blade::component('button', Button::class);
        Blade::component('badge', Badge::class);
        Blade::component('club-link', ClubLink::class);
        Blade::component('edit-button', EditButton::class);
        Blade::component('impression', Impression::class);
        Blade::component('modal', Modal::class);
        Blade::component('modal-button', ModalButton::class);
    }
}
