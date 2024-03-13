@php
    use Illuminate\Support\Str;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <title>{{ Str::limit($__env->yieldContent('title'), 20) }}</title>
    <style type="text/css">
        .tooltip-wrapper {
            position: relative;
            display: inline-block;
        }

        .tooltip-text {
            width: 200px;
            background-color: #555;
            color: #fff;
            text-align: center;
            padding: 5px 0;
            border-radius: 6px;
            position: absolute;
            z-index: 1;
            bottom: 100%;
            left: 50%;
            margin-left: -60px;

            opacity: 0;
            transition: opacity 0.3s;
            visibility: hidden;
        }

        .tooltip-wrapper:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body style="padding-bottom: 55px;">
@include('layouts.navbar')
<main>
    <div class="container-fluid">
        <h2 id="up">@yield('title')</h2>
        <div id="app">
            @yield('content')
        </div>
    </div>
</main>
<script src="{{ asset('js/app.js') }}"></script>
@include('layouts.script')
</body>
</html>
