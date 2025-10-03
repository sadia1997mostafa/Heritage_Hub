@extends('layouts.app')
@section('title','Admin Dashboard')

@section('content')
  <div class="hh-container pad-section">
    <h1 class="section-title">Admin Dashboard</h1>
    <p class="section-text">Welcome, <strong>{{ $admin->name }}</strong> ({{ $admin->email }})</p>

    <div class="card" style="padding:16px;border:1px solid #ddd;border-radius:12px;margin-top:12px">
      <ul>
        <li><a href="{{ route('home') }}">Go to site home</a></li>
        {{-- Add your admin links here --}}
      </ul>
    </div>
  </div>
@endsection
