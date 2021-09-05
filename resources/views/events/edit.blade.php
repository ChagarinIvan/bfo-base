@php
    use \App\Models\Event;
    use \App\Models\Flag;
    use Illuminate\Support\Collection;
    /**
     * @var Event $event;
     * @var Flag[]|Collection $flags;
     */
@endphp

@extends('layouts.app')

@section('title', __('app.event.edit'))

@section('content')
    <div class="row">
        <h1>{{ __('app.event.edit') }}</h1>
    </div>
    <form class="pt-5"
          method="POST"
          action="{{ action(\App\Http\Controllers\Event\UpdateEventAction::class, [$event]) }}"
          enctype="multipart/form-data"
    >
        @csrf
        <div class="form-group">
            <label for="name">{{ __('app.common.title') }}</label>
            <input class="form-control" id="name" name="name" value="{{ $event->name }}"/>
        </div>
        <div class="form-group">
            <label for="description">{{ __('app.competition.description') }}</label>
            <input class="form-control" id="description" name="description" value="{{ $event->description }}">
        </div>
        <div class="form-group row">
            <label for="date" class="col-2 col-form-label">{{ __('app.common.date') }}</label>
            <div class="col-10">
                <input class="form-control" type="date" id="date" name="date" value="{{ $event->date->format('Y-m-d') }}">
            </div>
        </div>

        <div class="accordion" id="accordion">
            <div class="card-header" id="headingOne">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed"
                            type="button"
                            data-toggle="collapse"
                            data-target="#collapseTwo"
                            aria-expanded="false"
                            aria-controls="collapseTwo"
                    >{{ __('app.protocol') }}</button>
                </h5>
            </div>
            <div id="collapseTwo" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                <div class="card-body">
                    <div class="form-group">
                        <input class="form-control" type="file" name="protocol"/>
                    </div>
                </div>
            </div>
            <div class="card-header" id="headingTwo">
                <h5 class="mb-0">
                    <button class="btn btn-link collapsed"
                            type="button"
                            data-toggle="collapse"
                            data-target="#collapseThree"
                            aria-expanded="false"
                            aria-controls="collapseThree"
                    >OBelarus.net</button>
                </h5>
            </div>
            <div id="collapseThree" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                <div class="card-body">
                    <div class="form-group">
                        <label for="obelarus-url">{{ __('app.common.url') }}</label>
                        <input class="form-control" id="obelarus-url" name="obelarus_net">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <input type="submit" class="btn btn-primary" value="{{ __('app.common.save') }}">
            <a href="{{ action(\App\Http\Controllers\BackAction::class) }}" class="btn btn-danger ml-1">{{ __('app.common.cancel') }}</a>
        </div>
    </form>
@endsection
