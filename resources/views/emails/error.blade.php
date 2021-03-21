@php
    /**
     * string $eventName
     * string $competitionName
     * string $error
     */
@endphp

<h3>{{ __('app.errors.title') }}.</h3>
<ul>
    <li>Памылка: {{ $error }}.</li>
    <li>Спаборництвы: {{ $competitionName }}.</li>
    <li>Івент: {{ $eventName }}.</li>
</ul>
