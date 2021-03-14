@section('navbar')
    <header>
        <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand {{ ('competitions' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/competitions">{{ __('app.navbar.competitions') }}</a>
            <a class="navbar-brand {{ ('persons' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/persons">{{ __('app.navbar.persons') }}</a>
            <a class="navbar-brand {{ ('club' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/club">{{ __('app.navbar.clubs') }}</a>
            <a class="navbar-brand {{ ('protocol-lines' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/protocol-lines/not-ident/show">{{ __('app.navbar.no-ident') }}</a>
            <a class="navbar-brand {{ ('flags' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/flags">{{ __('app.navbar.flags') }}</a>
            <a class="navbar-brand {{ ('faq' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/faq">{{ __('app.navbar.faq') }}</a>
            <a class="navbar-brand {{ App::isLocale('by') ? 'text-danger' : '' }}" href="/localization/by">{{ __('app.lang.by') }}</a>
            <a class="navbar-brand {{ App::isLocale('ru') ? 'text-danger' : ''}}" href="/localization/ru">{{ __('app.lang.ru') }}</a>
        </nav>
    </header>
@show
