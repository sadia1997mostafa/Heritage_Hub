@extends('layouts.app')
@section('title','Vendor Dashboard')

@section('content')
  <div class="hh-container pad-section">
    <h1 class="section-title">Vendor Dashboard</h1>

    <p class="section-text">
      Hello, <strong>{{ $user->name }}</strong> ({{ $user->email }})
    </p>

    @if($profile)
      <div class="card" style="padding:16px;border:1px solid #ddd;border-radius:12px;margin-top:12px">
        <h3 style="margin:0 0 8px 0">{{ $profile->shop_name }}</h3>
        <p style="margin:0 0 8px 0">{{ $profile->description }}</p>
        <p style="margin:0 0 8px 0"><strong>Phone:</strong> {{ $profile->phone }} | <strong>District:</strong> {{ $profile->district }}</p>
        <p style="margin:0"><strong>Address:</strong> {{ $profile->address }}</p>
      </div>
    @else
      <div class="card" style="padding:16px;border:1px solid #ddd;border-radius:12px;margin-top:12px">
        <p>No vendor profile found yet.</p>
      </div>
    @endif

    <div style="margin-top:16px">
      <a class="btn-primary" href="{{ route('home') }}">Go to site home</a>
    </div>
  </div>
@endsection
