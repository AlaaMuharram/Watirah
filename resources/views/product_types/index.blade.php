@extends('layouts.app')
@section('content')
<h3>Product Types</h3>
<form method="post" action="{{ route('product-types.store') }}" class="mb-3">@csrf
<input name="name" placeholder="Name" class="form-control mb-1" required>
<input name="unit_prep_minutes" type="number" step="0.01" placeholder="Unit prep minutes" class="form-control mb-1" required>
<button class="btn btn-primary">Add</button>
</form>
<table class="table"><thead><tr><th>Name</th><th>Unit prep min</th><th></th></tr></thead><tbody>
@foreach($types as $t)<tr><td>{{ $t->name }}</td><td>{{ $t->unit_prep_minutes }}</td><td><form method="post" action="{{ route('product-types.destroy',$t) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form></td></tr>@endforeach
</tbody></table>
@endsection