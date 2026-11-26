@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-2"><h3>{{ __('messages.Orders') }}</h3><a class="btn btn-primary" href="{{ route('orders.create') }}">{{ __('messages.Add') }}</a></div>
<form class="mb-2" method="get"><input name="search" class="form-control" placeholder="{{__('messages.Search')}}"></form>
<table class="table table-striped"><thead><tr><th>#</th><th>{{__('messages.Customer')}}</th><th>{{__('messages.Product')}}</th><th>{{__('messages.Quantity')}}</th><th>{{__('messages.Due Date')}}</th><th>{{__('messages.Status')}}</th><th></th></tr></thead><tbody>
@foreach($orders as $o)<tr>
<td>{{ $o->order_number }}</td><td>{{ $o->customer_name }}</td><td>{{ $o->product_name }}</td><td>{{ $o->quantity }}</td><td>{{ $o->due_date }}</td><td>{{ __($o->status) }}</td>
<td><a class="btn btn-sm btn-secondary" href="{{ route('orders.edit',$o) }}">{{__('messages.Edit')}}</a>
<form method="post" action="{{ route('orders.destroy',$o) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('{{__('messages.Confirm delete?')}}')">{{__('messages.Delete')}}</button></form></td>
</tr>@endforeach
</tbody></table>
@endsection