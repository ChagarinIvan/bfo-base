@php
    /**
     * @var \Throwable $error
     */
@endphp

<h3>{{ __('app.errors.title') }}.</h3>
<p>Code: {{ $error->getCode() }}</p>
<p>Message: {{ $error->getMessage() }}</p>
<p>Line: {{ $error->getLine() }}</p>
<p>Trace: @php var_dump($error->getTrace()) @endphp</p>
