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
        Blade::aliasComponent('button', Button::class);
        Blade::aliasComponent('badge', Badge::class);
        Blade::aliasComponent('club-link', ClubLink::class);
        Blade::aliasComponent('edit-button', EditButton::class);
        Blade::aliasComponent('impression', Impression::class);
        Blade::aliasComponent('modal', Modal::class);
        Blade::aliasComponent('modal-button', ModalButton::class);
    }
}
