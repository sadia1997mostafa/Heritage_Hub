@extends('layouts.vendor')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-4">Vendor - Return Requests</h1>
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    <div class="space-y-3">
        @foreach($returns as $r)
            <div class="p-3 bg-white rounded shadow flex justify-between">
                <div>
                    <div class="text-sm text-gray-600">#{{ $r->id }} • Order Item: {{ $r->order_item_id }}</div>
                    <div class="mt-1">{{ Str::limit($r->reason, 120) }}</div>
                    <div class="mt-2 text-sm">
                        @if($r->vendor_status === 'approved')
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded">Approved</span>
                        @elseif($r->vendor_status === 'declined')
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded">Declined</span>
                        @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded">Pending</span>
                        @endif
                    </div>
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
