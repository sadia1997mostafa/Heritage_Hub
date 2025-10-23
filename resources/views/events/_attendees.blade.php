@php
    // expects $event loaded with attendees relationship
    $count = $event->attendees->count();
    $slice = $event->attendees->take(6);
@endphp
<div class="mt-2 attendees" style="display:flex;gap:6px;align-items:center">
    @foreach($slice as $att)
        <img src="{{ $att->avatar_url }}" alt="{{ $att->name }}" title="{{ $att->name }}" style="width:28px;height:28px;border-radius:999px;object-fit:cover;border:2px solid #fff;box-shadow:0 6px 18px rgba(0,0,0,.12)">
    @endforeach
    @if($count > 6)
        <div class="event-badge">+{{ $count - 6 }}</div>
    @endif
</div>
