<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function index(Request $req)
    {
        $tab = $req->query('tab','all');
        $user = $req->user();

        if ($tab === 'mine' && $user) {
            $events = Event::where('user_id', $user->id)->latest()->paginate(15, ['*'], 'mine_page');
        } else {
            $events = Event::where('is_public', true)->latest()->paginate(15, ['*'], 'all_page');
        }

        return view('events.index', compact('events','tab'));
    }

    public function store(Request $req)
    {
        $req->validate([
            'title' => 'required|max:255',
            'description' => 'nullable|max:2000',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'location' => 'nullable|max:255',
            'cover_image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:5120',
        ]);

        $user = $req->user();
        if (!$user) return redirect()->route('login');

        $event = Event::create([
            'user_id' => $user->id,
            'title' => $req->title,
            'description' => $req->description,
            'starts_at' => $req->starts_at,
            'ends_at' => $req->ends_at,
            'location' => $req->location,
            'is_public' => true,
    
            'approved' => false,
        ]);

        // cover image
        if ($req->hasFile('cover_image')) {
            $f = $req->file('cover_image');
            $path = $f->store('events', 'public');
            $event->cover_image = $path;
            $event->save();
        }

        return redirect()->route('events.index', ['tab' => 'mine'])->with('status','Event created.');
    }

    public function show(Event $event)
    {
        $event->load('attendees','owner');
        return view('events.show', compact('event'));
    }

    // Toggle RSVP: set or update pivot row
    public function toggleRsvp(Request $req, Event $event)
    {
        $req->validate(['status' => 'required|in:interested,going']);
        $user = $req->user();
        if (!$user) return redirect()->route('login');

        $event->attendees()->syncWithoutDetaching([$user->id => ['status' => $req->status]]);


        if ($req->ajax()) {
            $event->load('attendees');
            $attHtml = view('events._attendees', compact('event'))->render();
            return response()->json([
                'interested' => $event->interestedCount(),
                'going' => $event->goingCount(),
                'status' => $req->status,
                'attendees_html' => $attHtml,
            ]);
        }

        return back();
    }

    // Admin: list all events including pending
    public function adminIndex(Request $req)
    {
        $this->authorize('viewAny', Event::class);
        $events = Event::with('owner')->orderBy('created_at','desc')->paginate(30);
        return view('admin.events.index', compact('events'));
    }

    public function approve(Request $req, Event $event)
    {
        $this->authorize('approve', $event);
        $event->approved = true;
        $approver = $req->user() ?: Auth::guard('admin')->user();
        $event->approved_by = $approver ? $approver->id : null;
        $event->approved_at = now();
        $event->save();

        // Notify owner
        try {
            if ($event->owner) {
                \App\Models\LocalNotification::create([
                    'user_id' => $event->owner->id,
                    'type' => 'event.approved',
                    'data' => ['message' => 'Your event "'.substr($event->title,0,80).'" was approved.', 'event_id' => $event->id, 'url' => route('events.show',$event->id)],
                    'is_read' => false,
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to create notification for event approval', ['err'=>$e->getMessage()]);
        }

        return back()->with('status','Event approved.');
    }
}
