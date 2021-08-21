@section('navbar')
    <header>
        <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand {{ ('competitions' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}"
               href="{{ action(\App\Http\Controllers\Competition\ShowCompetitionsTableAction::class, [App\Models\Year::actualYear()]) }}"
            >{{ __('app.navbar.competitions') }}</a>
            <a class="navbar-brand {{ ('cups' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/cups/y0">{{ __('app.navbar.cups') }}</a>
            <a class="navbar-brand {{ ('persons' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/persons">{{ __('app.navbar.persons') }}</a>
            <a class="navbar-brand {{ ('club' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/club">{{ __('app.navbar.clubs') }}</a>
            @auth
                <a class="navbar-brand {{ ('flags' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/flags">{{ __('app.navbar.flags') }}</a>
                <a class="navbar-brand {{ ('faq' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/faq">{{ __('app.navbar.faq') }}</a>
                <a class="navbar-brand {{ ('faq-api' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/faq-api">{{ __('app.navbar.api') }}</a>
            @endauth
            <a class="navbar-brand {{ App::isLocale('by') ? 'text-danger' : '' }}" href="/localization/by">{{ __('app.lang.by') }}</a>
            <a class="navbar-brand {{ App::isLocale('ru') ? 'text-danger' : ''}}" href="/localization/ru">{{ __('app.lang.ru') }}</a>
            @if(\App\Facades\System::isIdentRunning())
                <div class="spinner-border text-danger" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            @endif
            @if(Auth::check())
                <a class="navbar-brand text-info ml-auto" href="{{ action(\App\Http\Controllers\Registration\ShowRegistrationFormAction::class) }}">{{ __('app.common.registration') }}</a>
            @else
                <a class="navbar-brand text-info ml-auto" href="{{ action(\App\Http\Controllers\Login\ShowLoginFormAction::class) }}">{{ __('app.common.login') }}</a>
            @endif
        </nav>
    </header>
@show
