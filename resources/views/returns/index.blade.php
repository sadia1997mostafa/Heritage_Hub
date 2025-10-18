@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <h1 class="text-2xl font-semibold mb-4">Your Return Requests</h1>
    @if(session('success'))<div class="p-3 bg-green-100 text-green-800 rounded mb-4">{{ session('success') }}</div>@endif
    <div class="space-y-4">
        @forelse($returns as $r)
            <div class="p-4 border rounded flex items-start gap-4">
                <div class="flex-1">
                    <div class="text-sm text-gray-600">#{{ $r->id }} • {{ ucfirst($r->status) }} • {{ $r->created_at->diffForHumans() }}</div>
                    <div class="mt-2 text-gray-800">{{ Str::limit($r->reason, 200) }}</div>
                    @if($r->photos)
                        <div class="mt-3 flex gap-2">
                            @foreach($r->photos as $p)
                                <img src="{{ asset('storage/'.$p) }}" class="w-20 h-20 object-cover rounded" alt="photo"/>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="text-right">
                    <a href="#" class="text-sm text-blue-600">View</a>
                </div>
            </div>
        @empty
            <div class="p-4 bg-yellow-50 border rounded">You have not submitted any return requests yet.</div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $returns->links() }}
    </div>
</div>
@endsection
