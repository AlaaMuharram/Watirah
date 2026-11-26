@extends('layouts.app')
@section('content')
<h3>{{ __('messages.AI') }}</h3>
<form method="post" action="{{ route('ai.extract') }}">@csrf
<textarea name="text" class="form-control" rows="6" placeholder="Paste conversation...">{{ $text ?? '' }}</textarea>
<button class="btn btn-primary mt-2">Extract</button>
</form>
@if(isset($data))
<div class="mt-3 card p-3">
<p>Customer: {{ $data['customer_name'] }}</p>
<p>Product: {{ $data['product_name'] }}</p>
<p>Quantity: {{ $data['quantity'] }}</p>
<p>Due: {{ $data['due_date'] }}</p>
</div>
@endif
@endsection
