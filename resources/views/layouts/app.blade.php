<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ $currentLocale=='ar'?'rtl':'ltr' }}" id="htmlRoot">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>WATIRA</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--ink:#172a2a;--muted:#758381;--line:#e4eae6;--surface:#fff;--canvas:#f4f6f2;--green:#176b58;--green-deep:#124c41;--mint:#e5f2eb;--coral:#df795e;--gold:#dda84b;--shadow:0 12px 32px rgba(30,57,47,.055)}
*{box-sizing:border-box}
body{font-family:{{ $currentLocale=='ar'?"'IBM Plex Sans Arabic',sans-serif":"'DM Sans',sans-serif" }};background:var(--canvas);color:var(--ink);font-size:15px;min-height:100vh}
.navbar{background:rgba(255,255,255,.96)!important;border-bottom:1px solid var(--line);box-shadow:0 3px 16px rgba(35,57,48,.035);min-height:76px;padding:0;backdrop-filter:blur(14px)}
.navbar>.container-fluid{max-width:1480px;padding-inline:28px}
.navbar-brand{color:var(--ink)!important;font-size:19px;letter-spacing:1px;display:inline-flex;align-items:center;gap:11px}
.brand-mark{width:37px;height:37px;border-radius:11px;background:var(--green);display:grid;place-items:center;color:#fff;font-size:18px;box-shadow:0 5px 12px rgba(23,107,88,.2)}
.navbar-nav{align-items:center;gap:4px!important}
.navbar .nav-link{color:#65736f!important;font-size:13px;font-weight:600;padding:10px 12px!important;border-radius:8px;transition:background .18s,color .18s}
.navbar .nav-link:hover,.navbar .nav-link.active{background:var(--mint);color:var(--green)!important}
.navbar .nav-link .bi{margin-inline-end:5px;font-size:14px}
.navbar-toggler{border:1px solid var(--line);padding:7px 10px}
.navbar-toggler:focus{box-shadow:0 0 0 3px rgba(23,107,88,.12)}
.language-button{border:1px solid var(--line);background:#fff;color:var(--ink);font-weight:700;min-width:42px;height:38px;border-radius:8px}
.language-button:hover{background:var(--mint);color:var(--green);border-color:#bdd8c8}
.page-shell{max-width:1480px;margin:0 auto;padding:36px 28px 56px}
.page-heading{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:28px;animation:rise-in .45s ease both}
.page-heading h1{font-size:clamp(25px,3vw,34px);line-height:1.2;font-weight:700;margin:0;letter-spacing:0}
.page-heading p{color:var(--muted);margin:8px 0 0;font-size:14px}
.card{border:1px solid var(--line);border-radius:10px;background:var(--surface);box-shadow:var(--shadow);transition:box-shadow .2s,border-color .2s}
.card:hover{border-color:#d1dfd5;box-shadow:0 15px 34px rgba(30,57,47,.085)}
.btn{border-radius:8px;font-weight:600;padding:9px 15px;transition:transform .18s,background .18s}
.btn:hover{transform:translateY(-1px)}
.btn-primary{background:var(--green);border-color:var(--green)}
.btn-primary:hover,.btn-primary:focus{background:var(--green-deep);border-color:var(--green-deep)}
.btn-secondary{background:#eef2ef;border-color:#eef2ef;color:#42514d}
.btn-secondary:hover{background:#e3eae5;border-color:#e3eae5;color:var(--ink)}
.alert{border:0;border-radius:8px;padding:14px 18px;margin-bottom:22px}
.table-responsive{border:1px solid var(--line);border-radius:9px;background:#fff;overflow-x:auto}
.table{--bs-table-bg:transparent;--bs-table-striped-bg:#f8faf8;color:var(--ink);margin:0;vertical-align:middle}
.table thead th{background:#f6f8f6;color:#788580;font-size:11px;font-weight:700;letter-spacing:.55px;text-transform:uppercase;white-space:nowrap;padding:14px 16px;border-bottom:1px solid var(--line)}
.table tbody td{padding:14px 16px;border-color:#edf0ed;font-size:13px}
.table tbody tr:last-child td{border-bottom:0}
.table tbody tr:hover{--bs-table-bg:#fbfcfa}
.badge{border-radius:6px;padding:6px 9px;font-weight:600}
.form-control,.form-select{border:1px solid #dfe6e1;border-radius:8px;padding:10px 12px;font-size:14px;min-height:42px}
.form-control:focus,.form-select:focus{border-color:#80b6a0;box-shadow:0 0 0 3px rgba(23,107,88,.1)}
.dashboard-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:20px}
.metric-card{padding:20px;position:relative;overflow:hidden;animation:rise-in .5s ease both}
.metric-card:nth-child(2){animation-delay:.06s}.metric-card:nth-child(3){animation-delay:.12s}.metric-card:nth-child(4){animation-delay:.18s}
.metric-top{display:flex;align-items:center;justify-content:space-between;gap:10px}
.metric-label{color:var(--muted);font-size:13px;font-weight:600}
.metric-icon{width:38px;height:38px;border-radius:9px;display:grid;place-items:center;font-size:17px}
.metric-icon.green{background:#e5f2eb;color:var(--green)}.metric-icon.coral{background:#fff0eb;color:#c5654b}.metric-icon.gold{background:#fff5df;color:#aa7824}.metric-icon.teal{background:#e5f1f2;color:#427b7c}
.metric-value{font-size:31px;line-height:1.1;font-weight:700;margin-top:17px;letter-spacing:0;color:var(--ink)}
.metric-note{font-size:12px;color:var(--muted);margin-top:7px}
.dashboard-lower{display:grid;grid-template-columns:minmax(0,1fr) 290px;gap:18px;align-items:start}
.dashboard-lower>*{min-width:0}
.panel{border:1px solid var(--line);background:#fff;border-radius:10px;box-shadow:var(--shadow)}
.panel-heading{padding:18px 20px;display:flex;align-items:center;justify-content:space-between;gap:12px;border-bottom:1px solid var(--line)}
.panel-heading h2{font-size:16px;font-weight:700;margin:0}
.panel-body{padding:19px 20px}
.capacity-number{font-size:27px;font-weight:700;margin:7px 0 4px}
.capacity-label{color:var(--muted);font-size:12px}
.capacity-track{height:8px;border-radius:8px;background:#edf1ed;overflow:hidden;margin:17px 0 10px}
.capacity-fill{height:100%;border-radius:8px;background:linear-gradient(90deg,#27795f,#87b276);transition:width .6s ease}
.capacity-foot{display:flex;justify-content:space-between;color:var(--muted);font-size:11px}
.risk-pill{display:inline-flex;align-items:center;gap:6px;border-radius:99px;padding:5px 9px;font-size:11px;font-weight:700;background:#edf4ef;color:#39734e;white-space:nowrap}
.risk-pill::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor}
.risk-pill.medium{background:#fff5df;color:#a87925}.risk-pill.high{background:#fff0eb;color:#be5b46}
.empty-state{text-align:center;padding:42px 18px;color:var(--muted)}
.empty-state .bi{font-size:25px;color:#8aa095;display:block;margin-bottom:9px}
@keyframes rise-in{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:1100px){.navbar .nav-link{padding-inline:8px!important;font-size:12px}.navbar-nav{gap:0!important}.dashboard-lower{grid-template-columns:minmax(0,1fr) 250px}}
@media(max-width:991.98px){.navbar>.container-fluid{padding-inline:18px}.navbar-collapse{padding:12px 0 18px}.navbar-nav{align-items:stretch}.navbar .nav-link{padding:10px 12px!important;font-size:14px}.language-button{margin-top:8px}.page-shell{padding:28px 18px 42px}}
@media(max-width:760px){.dashboard-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:11px}.metric-card{padding:15px}.metric-value{font-size:26px}.dashboard-lower{grid-template-columns:minmax(0,1fr)}.page-heading{align-items:flex-start;flex-direction:column;margin-bottom:22px}.page-heading .btn{align-self:stretch}.panel-heading{padding:16px}.panel-body{padding:17px}}
@media(max-width:420px){.dashboard-grid{gap:9px}.metric-card{padding:13px}.metric-label{font-size:11px}.metric-icon{width:32px;height:32px;font-size:15px}.metric-value{font-size:23px}.page-shell{padding-inline:13px}}
@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important;scroll-behavior:auto!important}}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
<div class="container-fluid">
<a class="navbar-brand fw-bold" href="{{ route('dashboard') }}"><span class="brand-mark"><i class="bi bi-bar-chart-line-fill"></i></span> WATIRA</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavigation" aria-controls="mainNavigation" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="mainNavigation">
<ul class="navbar-nav me-auto">
<li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid"></i> {{ __('messages.Dashboard') }}</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}"><i class="bi bi-list-task"></i> {{ __('messages.Orders') }}</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('schedule.*') ? 'active' : '' }}" href="{{ route('schedule.index') }}"><i class="bi bi-calendar3"></i> {{ __('messages.Schedule') }}</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('simulator.*') ? 'active' : '' }}" href="{{ route('simulator.index') }}"><i class="bi bi-cpu"></i> {{ __('messages.Simulator') }}</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('delays.*') ? 'active' : '' }}" href="{{ route('delays.index') }}"><i class="bi bi-exclamation-triangle"></i> {{ __('messages.Delays') }}</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('ai.*') ? 'active' : '' }}" href="{{ route('ai.index') }}"><i class="bi bi-robot"></i> {{ __('messages.AI') }}</a></li>
<li class="nav-item"><a class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}" href="{{ route('settings.index') }}"><i class="bi bi-gear"></i> {{ __('messages.Settings') }}</a></li>
</ul>
<button id="langBtn" class="language-button" onclick="switchLang()" aria-label="Switch language">{{ $currentLocale=='ar'?'EN':'AR' }}</button>
</div>
</div></nav>
<main class="page-shell">@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @yield('content')</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const LOCALE='{{ $currentLocale }}';
function switchLang(){
  const next=LOCALE==='ar'?'en':'ar';
  fetch('/lang/'+next).then(()=>location.reload());
}
</script>
</body></html>
