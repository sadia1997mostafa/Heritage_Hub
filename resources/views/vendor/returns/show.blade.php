@extends('layouts.vendor')

@section('content')
<div class="max-w-3xl mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-2">Return Request #{{ $r->id }}</h1>
    @if(session('success'))
        <div class="mb-3 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    <div class="mb-3">
        @if($r->vendor_status === 'approved')
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded">Approved</span>
        @elseif($r->vendor_status === 'declined')
            <span class="px-3 py-1 bg-red-100 text-red-800 rounded">Declined</span>
        @else
            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded">Pending</span>
        @endif
    </div>
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

    <form id="vendor-return-form" method="POST" class="mt-4" action="{{ route('vendor.returns.approve', $r) }}">@csrf
        <label class="block text-sm">Vendor notes (optional)</label>
        <textarea name="vendor_notes" id="vendor_notes" class="block w-full border p-2 mt-1"></textarea>
        <div class="mt-3 flex gap-2">
            <button type="button" id="approve-btn" data-url="{{ route('vendor.returns.approve', $r) }}" class="px-4 py-2 bg-green-600 text-white rounded">Approve</button>
            <button type="button" id="decline-btn" data-url="{{ route('vendor.returns.decline', $r) }}" class="px-4 py-2 bg-red-600 text-white rounded">Decline</button>
        </div>
    </form>

    <script>
        (function(){
            const token = document.querySelector('input[name="_token"]').value;
            const approveBtn = document.getElementById('approve-btn');
            const declineBtn = document.getElementById('decline-btn');
            const notes = document.getElementById('vendor_notes');
            const statusBadge = document.querySelector('.mb-3 span');

            function submitAction(url, btn, desiredStatus) {
                btn.disabled = true;
                btn.textContent = desiredStatus === 'approved' ? 'Approving...' : 'Declining...';
                fetch(url, {
                    method: 'POST',
                    headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                    body: JSON.stringify({ vendor_notes: notes.value })
                }).then(r => r.json()).then(data => {
                    // Update UI
                    if (desiredStatus === 'approved') {
                        approveBtn.textContent = 'Approved';
                        approveBtn.classList.remove('bg-green-600');
                        approveBtn.classList.add('bg-gray-300','text-gray-800');
                        declineBtn.style.display = 'none';
                        if (statusBadge) statusBadge.textContent = 'Approved';
                    } else {
                        declineBtn.textContent = 'Declined';
                        declineBtn.classList.remove('bg-red-600');
                        declineBtn.classList.add('bg-gray-300','text-gray-800');
                        approveBtn.style.display = 'none';
                        if (statusBadge) statusBadge.textContent = 'Declined';
                    }
                }).catch(err => {
                    console.error('Approve/Decline failed', err);
                    btn.disabled = false;
                    btn.textContent = desiredStatus === 'approved' ? 'Approve' : 'Decline';
                    alert('Action failed. Check your connection or try again.');
                });
            }

            approveBtn.addEventListener('click', function(){ submitAction(this.dataset.url, this, 'approved'); });
            declineBtn.addEventListener('click', function(){ if (confirm('Are you sure you want to decline this return?')) submitAction(this.dataset.url, this, 'declined'); });
        })();
    </script>

    @if($r->vendor_notes)
        <div class="mt-4 p-3 bg-gray-50">Vendor notes: {{ $r->vendor_notes }}</div>
    @endif

    @if($r->admin_notes)
        <div class="mt-2 p-3 bg-yellow-50">Admin notes: {{ $r->admin_notes }}</div>
    @endif
</div>
@endsection
