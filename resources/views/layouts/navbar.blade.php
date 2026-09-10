@php
    use App\Bridge\Laravel\Http\Controllers\Cup\ShowCupsListAction;
    use App\Models\Year;
    /**
     * @var bool $isAuth;
     * @var bool $isCompetitionsRoute;
     * @var bool $isCupsRoute;
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
                    <a class="nav-link dropdown-toggle {{ ($isCompetitionsRoute || $isCupsRoute) ? 'active' : '' }}"
                       href="#"
                       id="competitionsDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"
                    >{{ __('app.navbar.competitions') }}</a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="competitionsDropdown">
                        <li>
                            <a class="dropdown-item {{ $isCompetitionsRoute ? 'active' : '' }}"
                               href="/app/competitions"
                            >{{ __('app.navbar.competitions') }}</a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ $isCupsRoute ? 'active' : '' }}"
                               href="{{ action(ShowCupsListAction::class, ['year' => (string)Year::actualYear()->value, 'visible' => true]) }}"
                            >{{ __('app.navbar.cups') }}</a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                               href="/app/groups"
                            >{{ __('app.common.groups') }}</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle"
                       href="#"
                       id="personsDropdown"
                       role="button"
                       data-bs-toggle="dropdown"
                       aria-expanded="false"
                    >{{ __('app.navbar.persons') }}</a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="personsDropdown">
                        <li>
                            <a class="dropdown-item"
                               href="/app/persons"
                            >{{ __('app.navbar.persons') }}</a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                               href="/app/clubs"
                            >{{ __('app.navbar.clubs') }}</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="d-flex ms-auto my-1">
                @if($isAuth)
                    <a class="btn btn-outline-secondary btn-sm me-2"
                       type="button"
                       href="/app/registration"
                    >{{ __('app.common.registration') }}</a>
                    <a class="btn btn-outline-danger btn-sm me-2"
                       type="button"
                       href="/app/competitions"
                    >{{ __('app.common.sign-out') }}</a>
                @else
                    <a class="btn btn-outline-info btn-sm me-2"
                       type="button"
                       href="/app/login"
                    >{{ __('app.common.login') }}</a>
                @endif
            </div>
        </div>
    </div>
</nav>
