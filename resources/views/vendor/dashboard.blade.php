@extends('layouts.vendor')

@section('title','Dashboard')

@section('content')
  <h1 class="text-2xl font-bold mb-4">Vendor Dashboard</h1>
  <div class="bg-gray-100 p-4 rounded">
    <h2 class="text-xl font-semibold">{{ $vendor->shop_name }}</h2>
    <p>{{ $vendor->description }}</p>
    <p><strong>Phone:</strong> {{ $vendor->phone }}</p>
    <p><strong>District:</strong> {{ $vendor->district->name ?? '-' }}</p>
    <p><strong>Address:</strong> {{ $vendor->address }}</p>
  </div>
@endsection
