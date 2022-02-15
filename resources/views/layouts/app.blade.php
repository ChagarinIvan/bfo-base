<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
<title>{{ \Illuminate\Support\Str::limit($__env->yieldContent('title'), 20) }}</title>
</head>
<body style="padding-bottom: 55px;">
    @include('layouts.navbar')
    <main>
        <div class="container-fluid">
            <h2 id="up">@yield('title')</h2>
            <div id="app" data-auth=@auth"1"@else"0"@endauth>
                @yield('content')
            </div>
        </div>
    </main>
    <script src="{{ asset('js/app.js') }}"></script>
    @include('layouts.script')
</body>
</html>
