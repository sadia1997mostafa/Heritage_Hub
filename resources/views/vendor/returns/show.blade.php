@extends('layouts.vendor')

@section('content')
<div class="max-w-3xl mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-2">Return Request #{{ $r->id }}</h1>
    <div class="mb-4 text-sm text-gray-600">Submitted: {{ $r->created_at->toDayDateTimeString() }}</div>

    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-medium">Reason</h3>
        <p class="mt-2">{{ $r->reason }}</p>

            @if($r->orderItem && $r->orderItem->product)
                @php $product = $r->orderItem->product; @endphp
                <div class="mb-4 flex gap-4">
                    <div class="w-32 h-32 bg-gray-100 rounded overflow-hidden">
                        <img src="{{ $product->first_image_url }}" alt="{{ $product->title }}" class="object-cover w-full h-full" />
                    </div>
                    <div class="flex-1">
                        <h2 class="text-lg font-semibold">{{ $product->title }}</h2>
                        @if(isset($product->price))<div class="text-sm text-gray-600">Price: &#2547;{{ number_format($product->price,2) }}</div>@endif
                        <div class="text-sm text-gray-500">Order Item #: {{ $r->order_item_id }}</div>
                        <div class="mt-2"><a href="{{ route('shop.product.show', $product->slug ?? '#') }}" class="text-blue-600 text-sm">View product</a></div>
                    </div>
                </div>
            @else
                <div class="mb-4 text-sm text-gray-500">Product information not available.</div>
            @endif

        @if($r->photos)
            <div class="mt-4 grid grid-cols-3 gap-3">
                @foreach($r->photos as $p)
                    <img src="{{ asset('storage/'.$p) }}" class="rounded object-cover h-40 w-full"/>
                @endforeach
            </div>
        @endif
    </div>

    <form method="POST" class="mt-4" action="{{ route('vendor.returns.approve', $r) }}">@csrf
        <label class="block text-sm">Vendor notes (optional)</label>
        <textarea name="vendor_notes" class="block w-full border p-2 mt-1"></textarea>
        <div class="mt-3 flex gap-2">
            <button formaction="{{ route('vendor.returns.approve', $r) }}" class="px-4 py-2 bg-green-600 text-white rounded">Approve</button>
            <button formaction="{{ route('vendor.returns.decline', $r) }}" class="px-4 py-2 bg-red-600 text-white rounded">Decline</button>
        </div>
    </form>

    @if($r->vendor_notes)
        <div class="mt-4 p-3 bg-gray-50">Vendor notes: {{ $r->vendor_notes }}</div>
    @endif

    @if($r->admin_notes)
        <div class="mt-2 p-3 bg-yellow-50">Admin notes: {{ $r->admin_notes }}</div>
    @endif
</div>
@endsection
