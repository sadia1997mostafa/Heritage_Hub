@extends('layouts.vendor')
@section('title','My Products')

@section('content')
<div class="card">
  {{-- Header row --}}
  <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
    <h2 style="margin:0">My Products</h2>
    <a href="{{ route('vendor.products.create') }}" class="btn primary">+ Add Product</a>
  </div>

  {{-- Flash message --}}
  @if(session('status'))
    <div style="margin-top:10px" class="badge" role="alert">{{ session('status') }}</div>
  @endif

  {{-- Table --}}
  <table class="table" style="margin-top:12px; width:100%">
    <thead>
      <tr>
        <th style="text-align:left; padding:10px">Title</th>
        <th style="text-align:left; padding:10px">Category</th>
        <th style="text-align:right; padding:10px">Price (৳)</th> {{-- NEW --}}
        <th style="text-align:right; padding:10px">Stock</th>
        <th style="text-align:center; padding:10px">Status</th>
        <th style="text-align:center; padding:10px; width:1%">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($products as $p)
        <tr>
          <td style="padding:10px">{{ $p->title }}</td>
          <td style="padding:10px">{{ $p->category->name ?? '—' }}</td>
          <td style="padding:10px; text-align:right">৳ {{ number_format($p->price, 2) }}</td> {{-- NEW --}}
          <td style="padding:10px; text-align:right">{{ $p->stock }}</td>
          <td style="padding:10px; text-align:center">
            <span class="badge {{ $p->status }}">{{ ucfirst($p->status) }}</span>
          </td>
          <td style="padding:10px; text-align:center; white-space:nowrap">
            @if($p->status === 'draft')
              <form method="POST" action="{{ route('vendor.products.submit', $p) }}" style="display:inline-block">
                @csrf
                <button class="btn ghost" title="Submit for approval">Submit</button>
              </form>
            @else
              —
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" style="padding:14px; text-align:center; color:var(--muted)">
            No products yet. <a class="btn primary" href="{{ route('vendor.products.create') }}" style="margin-left:8px">+ Add Product</a>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
