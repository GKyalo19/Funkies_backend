<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'eventClass',
        'level',
        'category',
        'subject',
        'name',
        'venue',
        'description',
        'startDate',
        'endDate',
        'hosts',
        'sponsors',
        'capacity',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'likes', 'event_id', 'user_id')->withTimestamps();
    }
    protected $casts = [
        'startDate' => 'datetime',
        'endDate' => 'datetime',
    ];

    protected $appends = ['isLiked'];

    protected $hidden = []; // optionally hide pivot data

    public function getIsLikedAttribute()
    {
        $user = Auth::user();
        if (!$user) return false;

        return $user->likedEvents()->where('event_id', $this->id)->exists();
    }
}
