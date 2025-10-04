@extends('layouts.vendor')

@section('title','Store Profile')

@section('content')
  <h1 class="text-2xl font-bold mb-4">Edit Store Profile</h1>
  <form method="POST" action="{{ route('vendor.store.setup.save') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    <input type="text" name="shop_name" value="{{ old('shop_name',$profile->shop_name) }}" placeholder="Shop name" class="form-control">
    <input type="email" name="support_email" value="{{ old('support_email',$profile->support_email) }}" placeholder="Support Email" class="form-control">
    <input type="text" name="support_phone" value="{{ old('support_phone',$profile->support_phone) }}" placeholder="Support Phone" class="form-control">
    <textarea name="description" class="form-control">{{ $profile->description }}</textarea>
    <input type="file" name="shop_logo" class="form-control">
    <input type="file" name="banner" class="form-control">
    <button class="btn btn-primary">Save</button>
  </form>
@endsection
