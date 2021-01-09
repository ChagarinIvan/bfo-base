@section('navbar')
    <header>
        <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
            <a class="navbar-brand {{ ('competitions' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/competitions">Соревнования</a>
            <a class="navbar-brand {{ ('persons' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/persons">Члены федерации</a>
            <a class="navbar-brand {{ ('protocol-lines' === request()->segment(1)) ? 'text-light' : 'text-secondary' }}" href="/protocol-lines/not-ident/show">Не идентифицированные записи протоколов</a>
        </nav>
    </header>
@show
