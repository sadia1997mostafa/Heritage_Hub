@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white shadow rounded">
    <h1 class="text-2xl font-bold mb-4">Vendor Dashboard</h1>

    <p>Hello, <strong>{{ $vendor->user->name }}</strong> ({{ $vendor->user->email }})</p>

    <div class="mt-4 p-4 border rounded bg-gray-50">
        <h2 class="text-lg font-semibold">{{ $vendor->shop_name }}</h2>
        <p>{{ $vendor->description }}</p>

        <p><strong>Phone:</strong> {{ $vendor->phone }}</p>
        <p><strong>District:</strong> {{ $vendor->district?->name ?? '—' }}</p>
        <p><strong>Address:</strong> {{ $vendor->address }}</p>

        @if($vendor->shop_logo_path)
            <p class="mt-2">
                <img src="{{ asset('storage/'.$vendor->shop_logo_path) }}" alt="Shop Logo" class="h-20">
            </p>
        @endif
    </div>

    <div class="mt-6 flex gap-3">
        <a href="{{ route('vendor.store.setup') }}" class="btn btn-primary">Edit Store Profile</a>
        <a href="{{ route('vendor.payout.form') }}" class="btn btn-secondary">Setup Payout</a>
    </div>
</div>
@endsection
