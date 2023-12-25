<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Auth\Guard as AuthManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Session\Session;

class UserService
{
    public const SESSION_LOCALE_PARAM_KEY = 'applocale';
    public const BY_LOCALE = 'by';
    public const RU_LOCALE = 'ru';

    public function __construct(
        readonly private Session $sessionManager,
        readonly private Application $application,
        readonly private AuthManager $authManager
    ) {
    }

    public function setLocale(string $locale): void
    {
        //        if ($locale === self::BY_LOCALE || $locale === self::RU_LOCALE) {
        //            $this->sessionManager->put(self::SESSION_LOCALE_PARAM_KEY, $locale);
        //        }
        $this->sessionManager->put(self::SESSION_LOCALE_PARAM_KEY, self::BY_LOCALE);
    }

    public function getLocale(): string
    {
        //        return $this->sessionManager->get(self::SESSION_LOCALE_PARAM_KEY, self::BY_LOCALE);
        return self::BY_LOCALE;
    }

    public function isByLocale(): bool
    {
        return $this->application->getLocale() === self::BY_LOCALE;
    }

    public function isRuLocale(): bool
    {
        return $this->application->getLocale() === self::RU_LOCALE;
    }

    public function isAuth(): bool
    {
        return $this->authManager->check();
    }
}
