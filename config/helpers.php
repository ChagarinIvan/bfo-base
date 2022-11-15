<?php

use App\Services\TranslateService;

if (! function_exists('t')) {
    /**
     * Translate.
     */
    function t(string $value)
    {
        return app(TranslateService::class)->translate($value);
    }
}
