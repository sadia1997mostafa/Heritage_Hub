<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    protected $fillable = ['user_id','title','description','starts_at','ends_at','location','is_public','approved','approved_by','approved_at','cover_image'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')
            ->withPivot('status')
            ->withTimestamps();
    }

    public function interestedCount()
    {
        return $this->attendees()->wherePivot('status','interested')->count();
    }

    public function goingCount()
    {
        return $this->attendees()->wherePivot('status','going')->count();
    }

    public function userStatus($userId)
    {
        return $this->attendees()->wherePivot('user_id',$userId)->first()?->pivot?->status ?? null;
    }
}
