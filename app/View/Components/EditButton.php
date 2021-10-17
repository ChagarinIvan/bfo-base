<?php

namespace App\View\Components;

class EditButton extends Button
{
    public function __construct(
        string $url = '#',
    ) {
        parent::__construct('app.common.edit', 'primary', 'bi-pencil', $url);
    }
}
