@extends('layouts.vendor')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-4">Vendor - Return Requests</h1>
    <div class="space-y-3">
        @foreach($returns as $r)
            <div class="p-3 bg-white rounded shadow flex justify-between">
                <div>
                    <div class="text-sm text-gray-600">#{{ $r->id }} • Order Item: {{ $r->order_item_id }}</div>
                    <div class="mt-1">{{ Str::limit($r->reason, 120) }}</div>
                </div>
                <div class="text-right">
                    <a href="{{ route('vendor.returns.show',$r) }}" class="text-blue-600">Review</a>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $returns->links() }}</div>
</div>
@endsection
