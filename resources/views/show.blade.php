@extends('laravel-messages::layouts.app')
@section('title', 'Message: '. $thread->subject)
@section('content')
	<h4>{{ $thread->subject }}</h4>
	<span class="d-inline-block mr-5">From: <strong>{{ $thread->user->name }}</strong></span>

	@if($thread->recipients)
		<span class="d-inline-block mr-5">To:
			@foreach($thread->recipients as $recipient)
				<strong>{{ $recipient->name }}</strong>
				{{ $thread->recipients->last()->id != $recipient->id ? ', ' : '' }}
			@endforeach
		</span>
	@endif

	<hr>

	@foreach($messages as $message)
		@include('laravel-messages::loop.message')
	@endforeach

	<form class="form-group" method="POST" action="{{ route(config('laravel-messages.route.name'). 'message.reply', $thread->id) }}">
		@csrf

		<div class="form-group{{ $errors->has('body') ? ' has-error' : '' }}">
			<label for="body" class="control-label">@lang('laravel-messages::strings.form.body')</label>
			<textarea id="body" name="body" class="form-control" rows="6" required>{{ old('body') }}</textarea>
			@if ($errors->has('body'))
				<span class="help-block">
		            <b>{{ $errors->first('body') }}</b>
		        </span>
			@endif
		</div>

		<div class="clearfix">
			<button type="submit" class="btn btn-success">@lang('laravel-messages::strings.form.send')</button>
		</div>
	</form>
@stop