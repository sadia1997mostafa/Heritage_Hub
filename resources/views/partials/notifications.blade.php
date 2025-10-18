@include('partials._notifications_css')
@php
  $unreadCount = auth()->check() ? \App\Models\LocalNotification::where('user_id', auth()->id())->where('is_read',false)->count() : 0;
  $items = auth()->check() ? \App\Models\LocalNotification::where('user_id', auth()->id())->latest()->limit(25)->get() : collect();
@endphp

<div class="hh-notify" id="hh-notify">
  <div class="icon" onclick="toggleNotify()">
    <svg width="20" height="20" viewBox="0 0 24 24"><path d="M12 22a2 2 0 0 0 2-2H10a2 2 0 0 0 2 2zM18 16v-5c0-3.07-1.63-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.63 5.36 6 7.92 6 11v5l-1.99 2H20l-2-2z" fill="currentColor"/></svg>
    @if($unreadCount)
      <span class="badge" id="notify-badge">{{ $unreadCount }}</span>
    @endif
  </div>

  <div class="dropdown" id="notify-dropdown">
    <div class="head">Notifications <button class="close" onclick="closeNotify()">&times;</button></div>
    <div class="list" id="notify-list">
      @if($items->count())
        @foreach($items as $it)
          <div class="item {{ $it->is_read ? '' : 'unread' }}" data-id="{{ $it->id }}">
            <div style="flex:1">
              @php
                $msg = $it->data['message'] ?? ucfirst(str_replace('_',' ',$it->type));
                $oid = $it->data['order_id'] ?? ($it->data['order_id'] ?? null);
              @endphp
              <div style="font-weight:700;color:#2b2b2b">
                @if($oid)
                  <a href="{{ route('orders.show', $oid) }}" style="color:inherit;text-decoration:none">{{ $msg }}</a>
                @else
                  {{ $msg }}
                @endif
              </div>
              <div class="meta">{{ $it->created_at->diffForHumans() }}</div>
            </div>
          </div>
        @endforeach
      @else
        <div class="empty">No notifications</div>
      @endif
    </div>
    <div class="actions"><button onclick="markAllRead()">Mark all as read</button></div>
  </div>
</div>

<script>
function toggleNotify(){
  const dd = document.getElementById('notify-dropdown');
  dd.classList.toggle('show');
}
function closeNotify(){
  const dd = document.getElementById('notify-dropdown');
  dd.classList.remove('show');
}
// close when clicking outside
document.addEventListener('click', function(e){
  const wrap = document.getElementById('hh-notify');
  if (!wrap) return;
  if (!wrap.contains(e.target)) {
    document.getElementById('notify-dropdown')?.classList.remove('show');
  }
});
async function markAllRead(){
  await fetch('{{ route('notifications.mark_all_read') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'}});
  document.getElementById('notify-badge')?.remove();
  document.querySelectorAll('#notify-list .item').forEach(i=>i.classList.remove('unread'));
}
</script>
