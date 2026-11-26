@extends('layouts.app')
@section('content')
<h3>{{ __('messages.Edit') }}</h3>
<form method="post" action="{{ route('orders.update',$order) }}">@csrf @method('PUT')
<div class="mb-2"><label>{{__('messages.Customer')}}</label><input name="customer_name" class="form-control" value="{{ $order->customer_name }}" required></div>
<div class="mb-2"><label>{{__('messages.Product')}}</label><input name="product_name" class="form-control" value="{{ $order->product_name }}" required></div>
<div class="mb-2"><label>{{__('messages.Quantity')}}</label><input name="quantity" type="number" class="form-control" value="{{ $order->quantity }}" required></div>
<div class="mb-2"><label>Unit prep minutes</label><input name="unit_prep_minutes" type="number" step="0.01" class="form-control" value="{{ $order->unit_prep_minutes }}" required></div>
<div class="mb-2"><label>{{__('messages.Due Date')}}</label><input name="due_date" type="date" class="form-control" value="{{ $order->due_date }}"></div>
<div class="mb-2"><label>{{__('messages.Status')}}</label><select name="status" class="form-control">@foreach(['new','pending','in_progress','completed','late'] as $st)<option value="{{ $st }}" @selected($order->status==$st)>{{ __($st=='in_progress'?'In Progress':ucfirst($st)) }}</option>@endforeach</select></div>
<div class="mb-2"><label>{{__('messages.Notes')}}</label><textarea name="notes" class="form-control">{{ $order->notes }}</textarea></div>
<button class="btn btn-primary">{{__('messages.Save')}}</button>
</form>
@endsection