@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white shadow rounded">
  <h2 class="text-xl font-bold mb-4">Apply as Vendor</h2>
  @if(session('status'))<div class="p-2 bg-green-100">{{ session('status') }}</div>@endif
  <form method="POST" action="{{ route('vendor.apply.submit') }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3"><label>Shop Name *</label><input name="shop_name" class="form-control" required></div>
    <div class="mb-3"><label>Phone *</label><input name="phone" class="form-control" required></div>

    <div class="mb-3">
      <label>Vendor Category *</label>
      <select name="vendor_category" class="form-select" required>
        <option value="">— select —</option>
        @foreach($categories as $cat)
          <option value="{{ $cat }}">{{ $cat }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3"><label>Support Email</label><input type="email" name="support_email" class="form-control"></div>
    <div class="mb-3"><label>Support Phone</label><input name="support_phone" class="form-control"></div>

    <div class="mb-3">
      <label>District</label>
      <select name="district_id" class="form-select">
        <option value="">— select —</option>
        @foreach($districts as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach
      </select>
    </div>

    <div class="mb-3"><label>Address</label><input name="address" class="form-control"></div>
    <div class="mb-3"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
    <div class="mb-3"><label>Heritage Story</label><textarea name="heritage_story" class="form-control"></textarea></div>
    <div class="mb-3"><label>Shop Logo</label><input type="file" name="shop_logo" class="form-control"></div>
    <button class="btn btn-primary">Submit Application</button>
  </form>
</div>
@endsection
