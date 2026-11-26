@extends('layouts.app')
@section('content')
<h3>{{ __('messages.Simulator') }}</h3>
<form method="post" action="{{ route('simulator.run') }}">@csrf
<div class="mb-2"><label>Product</label><input name="product_name" class="form-control" required></div>
<div class="mb-2"><label>Customer</label><input name="customer_name" class="form-control" required></div>
<div class="mb-2"><label>Quantity</label><input name="quantity" type="number" class="form-control" value="1" required></div>
<div class="mb-2"><label>Unit prep minutes</label><input name="unit_prep_minutes" type="number" step="0.01" class="form-control" value="30" required></div>
<div class="mb-2"><label>Due date</label><input name="due_date" type="date" class="form-control"></div>
<button class="btn btn-primary">Run</button>
</form>
@if(isset($before))
<div class="mt-3 card p-3">
<p>Before: {{ $before }}h / After: {{ $after }}h</p>
<p>Util before: {{ $utilBefore }}% / after: {{ $utilAfter }}%</p>
<p>Available: {{ $available }}h</p>
<p>Decision: {{ $decision }}</p>
<form method="post" action="{{ route('simulator.accept') }}">@csrf
<input type="hidden" name="customer_name" value="{{ $r->customer_name }}">
<input type="hidden" name="product_name" value="{{ $r->product_name }}">
<input type="hidden" name="quantity" value="{{ $r->quantity }}">
<input type="hidden" name="unit_prep_minutes" value="{{ $r->unit_prep_minutes }}">
<input type="hidden" name="due_date" value="{{ $r->due_date }}">
<button class="btn btn-success">Accept</button>
</form>
</div>
@endif
@endsection