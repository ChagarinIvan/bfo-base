@php
    /**
     * @var bool $isAuth;
     * @var bool $isByLocale;
     * @var bool $isRuLocale;
     * @var bool $isCompetitionsRoute;
     * @var bool $isCupsRoute;
     * @var bool $isPersonsRoute;
     * @var bool $isClubsRoute;
     * @var bool $isFlagsRoute;
     * @var bool $isFaqRoute;
     * @var bool $isFaqApiRoute;
     */
@endphp

@section('navbar')
    <header>
        <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand {{ $isCompetitionsRoute ? 'text-light' : 'text-secondary' }}"
               href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionsListAction::class, [App\Models\Year::actualYear()]) }}"
            >{{ __('app.navbar.competitions') }}</a>
            <a class="navbar-brand {{ $isCupsRoute ? 'text-light' : 'text-secondary' }}"
               href="{{ action(\App\Http\Controllers\Cups\ShowCupsListAction::class, [App\Models\Year::actualYear()]) }}"
            >{{ __('app.navbar.cups') }}</a>
            <a class="navbar-brand {{ $isPersonsRoute ? 'text-light' : 'text-secondary' }}"
               href="{{ action(\App\Http\Controllers\Person\ShowPersonsListAction::class) }}"
            >{{ __('app.navbar.persons') }}</a>
            <a class="navbar-brand {{ $isClubsRoute ? 'text-light' : 'text-secondary' }}"
               href="{{ action(\App\Http\Controllers\Club\ShowClubsListAction::class) }}"
            >{{ __('app.navbar.clubs') }}</a>
            @auth
                <a class="navbar-brand {{ $isFlagsRoute ? 'text-light' : 'text-secondary' }}"
                   href="{{ action(\App\Http\Controllers\Flags\ShowFlagsListAction::class) }}"
                >{{ __('app.navbar.flags') }}</a>
                <a class="navbar-brand {{ $isFaqRoute ? 'text-light' : 'text-secondary' }}"
                   href="{{ action(\App\Http\Controllers\Faq\ShowFaqAction::class) }}"
                >{{ __('app.navbar.faq') }}</a>
                <a class="navbar-brand {{ $isFaqApiRoute ? 'text-light' : 'text-secondary' }}"
                   href="{{ action(\App\Http\Controllers\Faq\ShowApiFaqAction::class) }}"
                >{{ __('app.navbar.api') }}</a>
            @endauth
            <a class="navbar-brand {{ $isByLocale ? 'text-danger' : '' }}"
               href="{{ action(\App\Http\Controllers\Localization\ChangeLanguageAction::class, [\App\Services\UserService::BY_LOCALE]) }}"
            >{{ __('app.lang.by') }}</a>
            <a class="navbar-brand {{ $isRuLocale ? 'text-danger' : ''}}"
               href="{{ action(\App\Http\Controllers\Localization\ChangeLanguageAction::class, [\App\Services\UserService::RU_LOCALE]) }}"
            >{{ __('app.lang.ru') }}</a>
            @if($isAuth)
                <a class="navbar-brand text-info ml-auto"
                   href="{{ action(\App\Http\Controllers\Registration\ShowRegistrationFormAction::class) }}"
                >{{ __('app.common.registration') }}</a>
            @else
                <a class="navbar-brand text-info ml-auto"
                   href="{{ action(\App\Http\Controllers\Login\ShowLoginFormAction::class) }}"
                >{{ __('app.common.login') }}</a>
            @endif
        </nav>
    </header>
@show
