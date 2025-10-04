@extends('layouts.admin')
@section('content')
<h1 class="text-2xl font-bold mb-4">Vendors</h1>

<h3 class="font-semibold mt-4">Pending</h3>
<table class="table">
  <tr><th>Shop</th><th>User</th><th>Category</th><th>District</th><th></th></tr>
  @foreach($pending as $p)
    <tr>
      <td>{{ $p->shop_name }}</td>
      <td>{{ $p->user->email }}</td>
      <td>{{ $p->vendor_category ?? '-' }}</td>
      <td>{{ $p->district->name ?? '-' }}</td>
      <td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.vendors.show',$p->id) }}">Review</a></td>
    </tr>
  @endforeach
</table>

<h3 class="font-semibold mt-6">Approved</h3>
<table class="table">
  <tr><th>Shop</th><th>User</th><th>Category</th><th>Approved</th></tr>
  @foreach($approved as $p)
    <tr><td>{{ $p->shop_name }}</td><td>{{ $p->user->email }}</td><td>{{ $p->vendor_category ?? '-' }}</td><td>{{ $p->approved_at }}</td></tr>
  @endforeach
</table>

<h3 class="font-semibold mt-6">Rejected</h3>
<table class="table">
  <tr><th>Shop</th><th>User</th><th>Reason</th></tr>
  @foreach($rejected as $p)
    <tr><td>{{ $p->shop_name }}</td><td>{{ $p->user->email }}</td><td>{{ $p->rejection_reason }}</td></tr>
  @endforeach
</table>
@endsection
