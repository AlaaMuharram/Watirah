@extends('layouts.app')
@section('content')
<h3>{{ __('messages.Delays') }}</h3>
<table class="table"><thead><tr><th>#</th><th>{{__('messages.Customer')}}</th><th>{{__('messages.Product')}}</th><th>{{__('messages.Risk')}}</th><th>{{__('messages.Due Date')}}</th></tr></thead><tbody>
@foreach($list as $o)<tr><td>{{ $o->order_number }}</td><td>{{ $o->customer_name }}</td><td>{{ $o->product_name }}</td><td>{{ __($o->risk_level) }}</td><td>{{ $o->due_date }}</td></tr>@endforeach
</tbody></table>
@endsection