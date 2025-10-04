@extends('layouts.vendor')
@section('title','Add Product')

@section('content')
<div class="card">
  <form method="POST" enctype="multipart/form-data" action="{{ route('vendor.products.store') }}">
    @csrf
    <div style="margin-bottom:10px">
      <label>Title</label>
      <input name="title" class="input" value="{{ old('title') }}">
      @error('title')<div class="text-red-600">{{ $message }}</div>@enderror
    </div>
    <div style="margin-bottom:10px">
      <label>Category</label>
      <select name="category_id" class="input">
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
      </select>
    </div>
    <div style="margin-bottom:10px">
      <label>Description</label>
      <textarea name="description" class="input" rows="3">{{ old('description') }}</textarea>
    </div>
    <div style="margin-bottom:10px">
      <label>Stock</label>
      <input type="number" name="stock" class="input" min="0" value="0">
    </div>
    <div style="margin-bottom:10px">
      <label>Images</label>
      <input type="file" name="images[]" multiple>
    </div>
    <button class="btn primary">Save Product</button>
  </form>
</div>
@endsection
