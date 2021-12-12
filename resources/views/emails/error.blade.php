@php
    /**
     * @var \Throwable $error
     * @var string $url
     * @var string $previous
     */
@endphp

<h3>{{ __('app.errors.title') }}.</h3>
<p>Code: {{ $error->getCode() }}</p>
<p>Message: {{ $error->getMessage() }}</p>
<p>Line: {{ $error->getLine() }}</p>
<p>Url: {{ $url }}</p>
<p>Prev-url: {{ $previous }}</p>
