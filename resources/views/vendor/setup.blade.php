@extends('layouts.app')
@section('content')
<div class="max-w-2xl mx-auto p-6 bg-white shadow rounded">
  <h2 class="text-xl font-bold mb-4">Store Profile</h2>
  @if(session('status'))<div class="p-2 bg-green-100">{{ session('status') }}</div>@endif
  <form method="POST" action="{{ route('vendor.store.setup.save') }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3"><label>Shop Name</label>
      <input name="shop_name" class="form-control" value="{{ old('shop_name',$profile->shop_name) }}">
    </div>

    <div class="mb-3">
      <label>Vendor Category *</label>
      <select name="vendor_category" class="form-select" required>
        @foreach($categories as $cat)
          <option value="{{ $cat }}" @selected($profile->vendor_category === $cat)>{{ $cat }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3"><label>Support Email</label>
      <input type="email" name="support_email" class="form-control" value="{{ old('support_email',$profile->support_email) }}">
    </div>
    <div class="mb-3"><label>Support Phone</label>
      <input name="support_phone" class="form-control" value="{{ old('support_phone',$profile->support_phone) }}">
    </div>
    <div class="mb-3"><label>District</label>
      <select name="district_id" class="form-select">
        @foreach($districts as $d)
          <option value="{{ $d->id }}" @selected($profile->district_id==$d->id)>{{ $d->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="mb-3"><label>Address</label><input name="address" class="form-control" value="{{ $profile->address }}"></div>
    <div class="mb-3"><label>Description</label><textarea name="description" class="form-control">{{ $profile->description }}</textarea></div>
    <div class="mb-3"><label>Heritage Story</label><textarea name="heritage_story" class="form-control">{{ $profile->heritage_story }}</textarea></div>
    <div class="mb-3"><label>Logo</label><input type="file" name="shop_logo" class="form-control"></div>
    <div class="mb-3"><label>Banner</label><input type="file" name="banner" class="form-control"></div>
    <button class="btn btn-success">Save</button>
  </form>
</div>
@endsection
