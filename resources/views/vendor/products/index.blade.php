@extends('layouts.vendor')
@section('title','My Products')
@section('content')
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <h2>My Products</h2>
    <a href="{{ route('vendor.products.create') }}" class="btn primary">+ Add Product</a>
  </div>
  <table class="table" style="margin-top:12px">
    <tr><th>Title</th><th>Category</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
    @foreach($products as $p)
      <tr>
        <td>{{ $p->title }}</td>
        <td>{{ $p->category->name }}</td>
        <td>{{ $p->stock }}</td>
        <td><span class="badge {{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
        <td>
          @if($p->status === 'draft')
            <form method="POST" action="{{ route('vendor.products.submit',$p) }}">@csrf
              <button class="btn ghost">Submit</button>
            </form>
          @else —
          @endif
        </td>
      </tr>
    @endforeach
  </table>
</div>
@endsection
