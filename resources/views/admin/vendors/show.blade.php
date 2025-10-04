@extends('layouts.admin')
@section('content')
<h1 class="text-xl font-bold mb-3">{{ $profile->shop_name }}</h1>
<p><strong>User:</strong> {{ $profile->user->email }}</p>
<p><strong>Category:</strong> {{ $profile->vendor_category ?? '-' }}</p>
<p><strong>Phone:</strong> {{ $profile->phone }}</p>
<p><strong>District:</strong> {{ $profile->district->name ?? '-' }}</p>
<p><strong>Description:</strong> {{ $profile->description }}</p>
<p><strong>Heritage Story:</strong> {{ $profile->heritage_story }}</p>

@if($profile->shop_logo_path)
  <img src="{{ asset('storage/'.$profile->shop_logo_path) }}" alt="logo" style="max-height:80px">
@endif
@if($profile->banner_path)
  <div class="mt-2"><img src="{{ asset('storage/'.$profile->banner_path) }}" alt="banner" style="max-width:100%"></div>
@endif

@if(session('status'))<div class="p-2 bg-green-100 my-2">{{ session('status') }}</div>@endif

<form class="mt-4" method="POST" action="{{ route('admin.vendors.approve',$profile->id) }}">
  @csrf
  <button class="btn btn-success">Approve</button>
</form>

<form class="mt-2" method="POST" action="{{ route('admin.vendors.reject',$profile->id) }}">
  @csrf
  <input name="reason" class="form-control mb-2" placeholder="Reason for rejection" required>
  <button class="btn btn-danger">Reject</button>
</form>
@endsection
