@extends('layouts.app')
@section('title', $category->name)

@push('styles') @vite(['resources/css/shop.css']) @endpush

@section('content')
<div class="container">
  <div class="page-head">
    <h1>{{ $category->name }}</h1>
    <form class="filters" method="GET">
      <label><input type="checkbox" name="in_stock" value="1" {{ request('in_stock')?'checked':'' }}> In stock</label>
      <input class="f-input" type="number" name="min" placeholder="Min ৳" value="{{ request('min') }}">
      <input class="f-input" type="number" name="max" placeholder="Max ৳" value="{{ request('max') }}">
      <select name="sort" class="f-input">
        <option value="new" {{ request('sort')==='new'?'selected':'' }}>Newest</option>
        <option value="price_asc" {{ request('sort')==='price_asc'?'selected':'' }}>Price ↑</option>
        <option value="price_desc" {{ request('sort')==='price_desc'?'selected':'' }}>Price ↓</option>
        <option value="title" {{ request('sort')==='title'?'selected':'' }}>Title A–Z</option>
      </select>
      <button class="btn primary">Filter</button>
    </form>
  </div>

  @if($products->count())
    <div class="grid">
      @foreach($products as $p)
        <x-product-card :p="$p"/>
      @endforeach
    </div>
    <div class="pagination">{{ $products->links() }}</div>
  @else
    <p>No products found in this category.</p>
  @endif
</div>
@endsection
