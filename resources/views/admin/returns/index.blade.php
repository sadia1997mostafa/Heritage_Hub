@extends('layouts.admin')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-semibold mb-4">Return Requests</h1>
    @if(session('success'))<div class="mb-4 p-3 bg-green-100 text-green-700">{{ session('success') }}</div>@endif
    <table class="w-full table-auto border">
        <thead class="bg-gray-50">
            <tr>
                <th class="p-2">ID</th>
                <th class="p-2">User</th>
                <th class="p-2">Order Item</th>
                <th class="p-2">Status</th>
                <th class="p-2">Submitted</th>
                <th class="p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($returns as $r)
            <tr class="border-t">
                <td class="p-2">{{ $r->id }}</td>
                <td class="p-2">{{ $r->user->name ?? $r->user_id }}</td>
                <td class="p-2">{{ $r->order_item_id }}</td>
                <td class="p-2">{{ ucfirst($r->status) }}</td>
                <td class="p-2">{{ $r->created_at->toDateTimeString() }}</td>
                <td class="p-2">
                    <a href="{{ route('admin.returns.show', $r) }}" class="text-blue-600">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $returns->links() }}</div>
</div>
@endsection
