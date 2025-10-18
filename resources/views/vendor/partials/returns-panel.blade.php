<div>
  <h3 style="margin:0 0 8px">Return Requests</h3>
  @if(!empty($returnRequests) && $returnRequests->count())
    <div class="muted">You have {{ $returnRequests->count() }} pending return request(s).</div>
    <ul style="margin-top:8px">
      @foreach($returnRequests as $rr)
        <li>#{{ $rr->id }} — Order Item #{{ $rr->order_item_id }} • {{ Str::limit($rr->reason,60) }} • {{ $rr->created_at->diffForHumans() }} — <a href="{{ route('vendor.returns.show',$rr) }}">Review</a></li>
      @endforeach
    </ul>
    <div style="margin-top:8px"><a href="{{ route('vendor.returns.index') }}" class="btn ghost">View all return requests</a></div>
  @else
    <p class="muted">No pending return requests.</p>
  @endif
</div>
