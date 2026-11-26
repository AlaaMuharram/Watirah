@extends('layouts.app')
@section('content')
<h3>{{ __('messages.Add') }}</h3>
<form method="post" action="{{ route('orders.store') }}">@csrf
<div class="mb-2"><label>{{__('messages.Customer')}}</label><input name="customer_name" class="form-control" required></div>
<div class="mb-2"><label>{{__('messages.Product')}}</label><input name="product_name" class="form-control" required></div>
<div class="mb-2"><label>{{__('messages.Quantity')}}</label><input name="quantity" type="number" class="form-control" value="1" required></div>
<div class="mb-2"><label>Unit prep minutes</label><input name="unit_prep_minutes" type="number" step="0.01" class="form-control" value="30" required></div>
<div class="mb-2"><label>{{__('messages.Due Date')}}</label><input name="due_date" type="date" class="form-control"></div>
<div class="mb-2"><label>{{__('messages.Notes')}}</label><textarea name="notes" class="form-control"></textarea></div>
<button class="btn btn-primary">{{__('messages.Save')}}</button>
</form>
@endsection