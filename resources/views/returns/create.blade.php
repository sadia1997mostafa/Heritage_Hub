@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-4">Request a Return</h1>

    <form action="{{ route('returns.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf
        <input type="hidden" id="order_item_id" name="order_item_id" value="{{ $order_item_id }}" />
        @if(empty($order_item_id) && !empty($orderItems) && $orderItems->count())
            <div class="mb-3">
                <label class="block text-sm font-medium">Select item to return</label>
                <select id="orderItemSelect" class="mt-1 block w-full border p-2">
                    <option value="">-- choose an item --</option>
                    @foreach($orderItems as $oi)
                        <option value="{{ $oi->id }}">#{{ $oi->id }} — {{ $oi->product->title ?? 'Product' }} (Qty: {{ $oi->quantity ?? 1 }})</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if($errors->any())
            <div class="p-3 bg-red-50 text-red-700">{{ $errors->first() }}</div>
        @endif
        <div class="mb-2 text-sm text-gray-600">Order Item ID: <strong>{{ $order_item_id ?? 'not provided' }}</strong></div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Reason</label>
            <textarea name="reason" rows="4" class="mt-1 block w-full border rounded p-2">{{ old('reason') }}</textarea>
            @error('reason')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Photos (optional)</label>
            <input type="file" name="photos[]" multiple accept="image/*" class="mt-1" />
            @error('photos.*')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
        </div>

        <div class="flex justify-end">
            <a href="{{ route('returns.index') }}" class="mr-3 text-gray-600">Cancel</a>
            <button class="bg-brown-700 text-white px-4 py-2 rounded">Submit Return</button>
        </div>
    </form>
</div>
@if(empty($order_item_id) && !empty($orderItems) && $orderItems->count())
<script>
document.addEventListener('DOMContentLoaded', function(){
    const sel = document.getElementById('orderItemSelect');
    const hid = document.getElementById('order_item_id');
    if(sel && hid){
        sel.addEventListener('change', function(){ hid.value = this.value; });
    }
});
</script>
@endif
@endsection
