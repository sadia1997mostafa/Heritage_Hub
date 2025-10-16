@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-3xl">
    <h2 class="text-2xl font-semibold mb-3">Return Request #{{ $r->id }}</h2>
    <div class="mb-4 text-sm text-gray-600">Status: <strong>{{ ucfirst($r->status) }}</strong></div>
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-medium">Reason</h3>
        <p class="mt-2 text-gray-800">{{ $r->reason }}</p>

        @if($r->photos)
            <div class="mt-4 grid grid-cols-3 gap-3">
                @foreach($r->photos as $p)
                    <img src="{{ asset('storage/'.$p) }}" class="rounded object-cover h-40 w-full"/>
                @endforeach
            </div>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.returns.approve', $r) }}" class="mt-4">@csrf
        <label class="block text-sm">Admin notes (optional)</label>
        <textarea name="admin_notes" class="block w-full border p-2 mt-1"></textarea>
        <div class="mt-3 flex gap-2">
            <button formaction="{{ route('admin.returns.approve', $r) }}" class="px-4 py-2 bg-green-600 text-white rounded">Approve</button>
            <button formaction="{{ route('admin.returns.decline', $r) }}" class="px-4 py-2 bg-red-600 text-white rounded">Decline</button>
        </div>
    </form>
</div>
@endsection
