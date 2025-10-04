@extends('layouts.admin')

@section('content')
<div class="p-6 bg-white rounded shadow">
    <h1 class="text-2xl font-bold mb-4">Admin Dashboard</h1>

    <p class="mb-6">Welcome, <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})</p>

    <ul class="space-y-2">
        <li>
            <a href="{{ route('admin.vendors.index') }}" class="btn btn-primary">
                Manage Vendors
            </a>
        </li>
        <li>
            <a href="{{ route('home') }}" class="btn btn-secondary">
                Go to Site Home
            </a>
        </li>
    </ul>
</div>
@endsection
