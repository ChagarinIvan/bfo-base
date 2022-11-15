<?php

use App\Services\TranslateService;

if (! function_exists('t')) {
    /**
     * Translate.
     */
    function t(string $value)
    {
        /** @var TranslateService $service */
        $service = app(TranslateService::class);

        return $service->translate($value);
    }
}
