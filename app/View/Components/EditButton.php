<?php

declare(strict_types=1);

namespace App\View\Components;

final class EditButton extends Button
{
    public function __construct(
        string $url = '#',
    ) {
        parent::__construct('app.common.edit', 'primary', 'bi-pencil', $url);
    }
}
