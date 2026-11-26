@extends('layouts.app')
@section('content')
<h3>{{ __('messages.Settings') }}</h3>
<form method="post" action="{{ route('settings.store') }}">@csrf
<div class="mb-2"><label>Project name</label><input name="project_name" class="form-control" value="{{ $s->project_name }}"></div>
<div class="mb-2"><label>Daily hours</label><input name="daily_production_hours" type="number" step="0.5" class="form-control" value="{{ $s->daily_production_hours }}"></div>
<div class="mb-2"><label>Language</label><select name="locale" class="form-control"><option value="ar" @selected($s->locale=='ar')>AR</option><option value="en" @selected($s->locale=='en')>EN</option></select></div>
<button class="btn btn-primary">{{__('messages.Save')}}</button>
</form>
@endsection