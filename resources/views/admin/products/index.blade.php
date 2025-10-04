@extends('layouts.admin')
@section('title','Product Approvals')

@section('content')
@if(session('status'))
  <div class="card">{{ session('status') }}</div>
@endif

<div class="card" style="margin-bottom:20px">
  <h3>Pending Products</h3>
  <table class="table">
    <tr><th>Title</th><th>Vendor</th><th>Category</th><th>Actions</th></tr>
    @foreach($pending as $p)
      <tr>
        <td>{{ $p->title }}</td>
        <td>{{ $p->vendor->user->email }}</td>
        <td>{{ $p->category->name }}</td>
        <td>
          <form method="POST" action="{{ route('admin.products.approve',$p) }}">@csrf<button class="btn primary">Approve</button></form>
          <form method="POST" action="{{ route('admin.products.reject',$p) }}" style="display:inline">@csrf<button class="btn danger">Reject</button></form>
        </td>
      </tr>
    @endforeach
  </table>
</div>

<div class="card">
  <h3>Approved Products</h3>
  <table class="table">
    <tr><th>Title</th><th>Category</th><th>Price (৳)</th></tr>
    @foreach($approved as $p)
      <tr><td>{{ $p->title }}</td><td>{{ $p->category->name }}</td><td>৳ {{ number_format($p->price, 2) }}</td></tr>
    @endforeach
  </table>
</div>
@endsection
