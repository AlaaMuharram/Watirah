@extends('layouts.app')
@section('content')
<h3>{{ __('messages.Schedule') }}</h3>
<table class="table"><thead><tr><th>#</th><th>{{__('messages.Customer')}}</th><th>{{__('messages.Product')}}</th><th>Prep h</th><th>{{__('messages.Due Date')}}</th><th>{{__('messages.Status')}}</th></tr></thead><tbody>
@foreach($orders as $o)<tr><td>{{ $o->order_number }}</td><td>{{ $o->customer_name }}</td><td>{{ $o->product_name }}</td><td>{{ round($o->total_prep_minutes/60,2) }}</td><td>{{ $o->due_date }}</td><td>{{ __($o->status) }}</td></tr>@endforeach
</tbody></table>
@endsection