@extends('layouts.admin')
@section('content')
<div class="container">
  <h1>Pending Reviews</h1>
  @foreach($reviews as $r)
    <div class="p-3 bg-white rounded shadow mb-3">
      <div class="flex justify-between">
        <div><strong>{{ $r->user->name ?? 'Customer' }}</strong> — {{ $r->product->title ?? '' }}</div>
        <div>
          <form method="POST" action="{{ route('admin.reviews.approve', $r) }}" style="display:inline">@csrf<button class="btn">Approve</button></form>
          <form method="POST" action="{{ route('admin.reviews.hide', $r) }}" style="display:inline">@csrf<button class="btn">Hide</button></form>
        </div>
      </div>
      <div class="mt-2">Rating: {{ $r->rating }}</div>
      @if($r->body)<div class="mt-2">{{ $r->body }}</div>@endif
    </div>
  @endforeach
  {{ $reviews->links() }}
</div>
@endsection
