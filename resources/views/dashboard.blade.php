@extends('layouts.app')
@section('content')
<div class="page-heading">
	<div>
		<h1>{{ __('messages.Dashboard') }}</h1>
		<p>{{ $currentLocale=='ar' ? 'نظرة واضحة على الطلبات والطاقة الإنتاجية' : 'A clear view of orders and production capacity' }}</p>
	</div>
	<a class="btn btn-primary" href="{{ route('orders.create') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> {{ __('messages.Add') }}</a>
</div>
<section class="dashboard-grid" aria-label="{{ __('messages.Dashboard') }}">
	<article class="card metric-card">
		<div class="metric-top"><span class="metric-label">{{ $currentLocale=='ar' ? 'إجمالي الطلبات' : 'Total orders' }}</span><span class="metric-icon green"><i class="bi bi-box-seam" aria-hidden="true"></i></span></div>
		<div class="metric-value">{{ $orders->count() }}</div>
		<div class="metric-note">{{ $currentLocale=='ar' ? 'إجمالي الطلبات المسجلة' : 'Orders currently on the books' }}</div>
	</article>
	<article class="card metric-card">
		<div class="metric-top"><span class="metric-label">{{ $currentLocale=='ar' ? 'ساعات العمل المطلوبة' : 'Required hours' }}</span><span class="metric-icon coral"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span></div>
		<div class="metric-value">{{ number_format($required, 1) }}</div>
		<div class="metric-note">{{ $currentLocale=='ar' ? 'لتنفيذ الطلبات الحالية' : 'To complete current orders' }}</div>
	</article>
	<article class="card metric-card">
		<div class="metric-top"><span class="metric-label">{{ $currentLocale=='ar' ? 'الطاقة المتاحة' : 'Available capacity' }}</span><span class="metric-icon teal"><i class="bi bi-calendar2-check" aria-hidden="true"></i></span></div>
		<div class="metric-value">{{ number_format($available, 1) }}</div>
		<div class="metric-note">{{ $currentLocale=='ar' ? 'ساعة خلال 30 يوماً' : 'Hours over the next 30 days' }}</div>
	</article>
	<article class="card metric-card">
		<div class="metric-top"><span class="metric-label">{{ $currentLocale=='ar' ? 'نسبة استغلال الطاقة' : 'Capacity utilization' }}</span><span class="metric-icon gold"><i class="bi bi-speedometer2" aria-hidden="true"></i></span></div>
		<div class="metric-value">{{ number_format($util, 1) }}<span style="font-size:17px;color:var(--muted)">%</span></div>
		<div class="metric-note">{{ $currentLocale=='ar' ? 'من إجمالي الطاقة المتاحة' : 'Of total available capacity' }}</div>
	</article>
</section>
<section class="dashboard-lower">
	<div class="panel">
		<div class="panel-heading">
			<h2>{{ $currentLocale=='ar' ? 'الطلبات الأخيرة' : 'Recent orders' }}</h2>
			<a class="btn btn-secondary btn-sm" href="{{ route('orders.index') }}">{{ $currentLocale=='ar' ? 'عرض الكل' : 'View all' }} <i class="bi bi-arrow-up-left" aria-hidden="true"></i></a>
		</div>
		@if($orders->isEmpty())
			<div class="empty-state"><i class="bi bi-inboxes" aria-hidden="true"></i><div>{{ $currentLocale=='ar' ? 'لا توجد طلبات حتى الآن' : 'No orders have been added yet' }}</div></div>
		@else
			<div class="table-responsive" style="border:0;border-radius:0;box-shadow:none">
				<table class="table">
					<thead><tr><th>#</th><th>{{ __('messages.Customer') }}</th><th>{{ __('messages.Product') }}</th><th>{{ __('messages.Quantity') }}</th><th>{{ __('messages.Due Date') }}</th><th>{{ __('messages.Status') }}</th><th>{{ __('messages.Risk') }}</th></tr></thead>
					<tbody>
					@foreach($orders->take(8) as $o)
						@php($riskClass = in_array(strtolower((string) $o->risk_level), ['high', 'عالي', 'مرتفع'], true) ? 'high' : (in_array(strtolower((string) $o->risk_level), ['medium', 'متوسط'], true) ? 'medium' : ''))
						<tr><td class="fw-semibold">{{ $o->order_number }}</td><td>{{ $o->customer_name }}</td><td>{{ $o->product_name }}</td><td>{{ number_format($o->quantity) }}</td><td>{{ $o->due_date }}</td><td>{{ __($o->status) }}</td><td><span class="risk-pill {{ $riskClass }}">{{ __($o->risk_level) }}</span></td></tr>
					@endforeach
					</tbody>
				</table>
			</div>
		@endif
	</div>
	<aside class="panel">
		<div class="panel-heading"><h2>{{ $currentLocale=='ar' ? 'ملخص الطاقة' : 'Capacity overview' }}</h2><i class="bi bi-activity" style="color:var(--green)" aria-hidden="true"></i></div>
		<div class="panel-body">
			<div class="capacity-label">{{ $currentLocale=='ar' ? 'الطاقة المستخدمة' : 'Capacity in use' }}</div>
			<div class="capacity-number">{{ number_format($util, 1) }}%</div>
			<div class="capacity-label">{{ $currentLocale=='ar' ? 'من الساعات المتاحة خلال 30 يوماً' : 'of available hours over 30 days' }}</div>
			<div class="capacity-track" role="progressbar" aria-label="{{ $currentLocale=='ar' ? 'استغلال الطاقة' : 'Capacity utilization' }}" aria-valuenow="{{ min(100, max(0, $util)) }}" aria-valuemin="0" aria-valuemax="100"><div class="capacity-fill" style="width:{{ min(100, max(0, $util)) }}%"></div></div>
			<div class="capacity-foot"><span>{{ $currentLocale=='ar' ? 'مستخدم' : 'Used' }} · {{ number_format($required, 1) }} h</span><span>{{ $currentLocale=='ar' ? 'متاح' : 'Available' }} · {{ number_format($available, 1) }} h</span></div>
		</div>
	</aside>
</section>
@endsection
