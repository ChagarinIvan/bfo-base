@php
    use App\Bridge\Laravel\Http\Controllers\Club\ShowClubsListAction;
    use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionsListAction;
    use App\Bridge\Laravel\Http\Controllers\Cups\ShowCupsListAction;
    use App\Bridge\Laravel\Http\Controllers\Faq\ShowApiFaqAction;
    use App\Bridge\Laravel\Http\Controllers\Faq\ShowFaqAction;
    use App\Bridge\Laravel\Http\Controllers\Flags\ShowFlagsListAction;
    use App\Bridge\Laravel\Http\Controllers\Groups\ShowGroupsListAction;
    use App\Bridge\Laravel\Http\Controllers\Login\ShowLoginFormAction;
    use App\Bridge\Laravel\Http\Controllers\Login\SignOutAction;
    use App\Bridge\Laravel\Http\Controllers\Person\ShowPersonsListAction;
    use App\Bridge\Laravel\Http\Controllers\Rank\ShowRanksListAction;
    use App\Bridge\Laravel\Http\Controllers\Registration\ShowRegistrationFormAction;
    use App\Models\Rank;use App\Models\Year;
    /**
     * @var bool $isAuth;
     * @var bool $isByLocale;
     * @var bool $isRuLocale;
     * @var bool $isCompetitionsRoute;
     * @var bool $isCupsRoute;
     * @var bool $isPersonsRoute;
     * @var bool $isClubsRoute;
     * @var bool $isRanksRoute;
     * @var bool $isFlagsRoute;
     * @var bool $isFaqRoute;
     * @var bool $isGroupsRoute;
     * @var bool $isFaqApiRoute;
     */
@endphp

<nav class="navbar sticky-top navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">OrientBase</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto my-1">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ ($isCompetitionsRoute || $isCupsRoute || $isGroupsRoute || $isFlagsRoute) ? 'active' : '' }}"
                       href="#"
                       id="competitionsDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"
                    >{{ __('app.navbar.competitions') }}</a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="competitionsDropdown">
                        <li>
                            <a class="dropdown-item {{ $isCompetitionsRoute ? 'active' : '' }}"
                               href="{{ action(ShowCompetitionsListAction::class, [(string)Year::actualYear()->value]) }}"
                            >{{ __('app.navbar.competitions') }}</a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ $isCupsRoute ? 'active' : '' }}"
                               href="{{ action(ShowCupsListAction::class, [(string)Year::actualYear()->value]) }}"
                            >{{ __('app.navbar.cups') }}</a>
                        </li>
                        @auth
                            <li>
                                <a class="dropdown-item {{ $isGroupsRoute ? 'active' : '' }}"
                                   href="{{ action(ShowGroupsListAction::class) }}"
                                >{{ __('app.common.groups') }}</a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $isFlagsRoute ? 'active' : '' }}"
                                   href="{{ action(ShowFlagsListAction::class) }}"
                                >{{ __('app.navbar.flags') }}</a>
                            </li>
                        @endauth
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ ($isPersonsRoute || $isClubsRoute || $isRanksRoute) ? 'active' : '' }}"
                       href="#"
                       id="personsDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"
                    >{{ __('app.navbar.persons') }}</a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="personsDropdown">
                        <li>
                            <a class="dropdown-item {{ $isPersonsRoute ? 'active' : '' }}"
                               href="{{ action(ShowPersonsListAction::class) }}"
                            >{{ __('app.navbar.persons') }}</a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ $isClubsRoute ? 'active' : '' }}"
                               href="{{ action(ShowClubsListAction::class) }}"
                            >{{ __('app.navbar.clubs') }}</a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ $isRanksRoute ? 'active' : '' }}"
                               href="{{ action(ShowRanksListAction::class, [Rank::SM_RANK]) }}"
                            >{{ __('app.navbar.ranks') }}</a>
                        </li>
                    </ul>
                </li>
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ ($isFaqRoute || $isFaqApiRoute) ? 'active' : '' }}"
                           href="#"
                           id="apiDropdown"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false"
                        >{{ __('app.navbar.help') }}</a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="apiDropdown">
                            <li>
                                <a class="dropdown-item {{ $isFaqRoute ? 'active' : '' }}"
                                   href="{{ action(ShowFaqAction::class) }}"
                                >{{ __('app.navbar.faq') }}</a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $isFaqApiRoute ? 'active' : '' }}"
                                   href="{{ action(ShowApiFaqAction::class) }}"
                                >{{ __('app.navbar.api') }}</a>
                            </li>
                        </ul>
                    </li>
                @endauth
            </ul>
            {{--            <div class="d-flex mx-auto my-1">--}}
            {{--                <div class="btn-group btn-group-sm" role="group" aria-label="Select locale">--}}
            {{--                    <a type="button"--}}
            {{--                       class="btn {{ $isByLocale ? 'btn-outline-danger' : 'btn-outline-secondary' }}"--}}
            {{--                       href="{{ action(ChangeLanguageAction::class, [UserService::BY_LOCALE]) }}"--}}
            {{--                    >{{ __('app.lang.by') }}</a>--}}
            {{--                    <a type="button"--}}
            {{--                       class="btn btn-sm {{ $isRuLocale ? 'btn-outline-danger' : 'btn-outline-secondary' }}"--}}
            {{--                       href="{{ action(ChangeLanguageAction::class, [UserService::RU_LOCALE]) }}"--}}
            {{--                    >{{ __('app.lang.ru') }}</a>--}}
            {{--                </div>--}}
            {{--            </div>--}}

            <div class="d-flex ms-auto my-1">
                @if($isAuth)
                    <a class="btn btn-outline-secondary btn-sm me-2"
                       type="button"
                       href="{{ action(ShowRegistrationFormAction::class) }}"
                    >{{ __('app.common.registration') }}</a>
                    <a class="btn btn-outline-danger btn-sm me-2"
                       type="button"
                       href="{{ action(SignOutAction::class) }}"
                    >{{ __('app.common.sign-out') }}</a>
                @else
                    <a class="btn btn-outline-info btn-sm me-2"
                       type="button"
                       href="{{ action(ShowLoginFormAction::class) }}"
                    >{{ __('app.common.login') }}</a>
                @endif
            </div>
        </div>
    </div>
</nav>
