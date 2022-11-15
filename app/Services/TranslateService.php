<?php

namespace App\Services;

class TranslateService
{
    public function translate(string $value, string $locale = UserService::BY_LOCALE): string
    {
        return $value;
    }
}
