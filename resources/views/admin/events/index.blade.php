@extends('layouts.admin')

@section('content')
<div class="container">
    <h2>Event moderation</h2>
    <table class="table">
        <thead><tr><th>ID</th><th>Title</th><th>Owner</th><th>Approved</th><th>Action</th></tr></thead>
        <tbody>
        @foreach($events as $e)
            <tr>
                <td>{{ $e->id }}</td>
                <td>{{ $e->title }}</td>
                <td>{{ $e->owner->name ?? 'Unknown' }}</td>
                <td>{{ $e->approved ? 'Yes' : 'No' }}</td>
                <td>
                    @if(!$e->approved)
                        <form method="POST" action="{{ route('admin.events.approve', $e) }}">
                            @csrf
                            <button class="btn btn-primary">Approve</button>
                        </form>
                    @else
                        —
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-3">{{ $events->links() }}</div>
</div>
@endsection
