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
        'poster',
        'participation_mode',
        'venue',
        'link',
        'county',
        'description',
        'startDate',
        'endDate',
        'hosts',
        'sponsors',
        'capacity',
        'registration_fee',
        'currency',
        'contact_number',
        'user_id',
    ];

    public function getPosterUrlAttribute()
    {
        return $this->poster ? asset('storage/' . $this->poster) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'likes', 'event_id', 'user_id')->withTimestamps();
    }

    protected $appends = ['isLiked', 'posterUrl', 'isPaid'];

    public function getIsLikedAttribute()
    {
        $user = Auth::user();
        if (!$user) return false;

        return $user->likedEvents()->where('event_id', $this->id)->exists();
    }

    public function getIsPaidAttribute()
    {
        $user = Auth::user();
        if (!$user) return false;

        return $this->paidUsers()->where('user_id', $user->id)->exists();
    }


    public function paidUsers()
    {
        return $this->belongsToMany(User::class, 'event_user_payments')
            ->withPivot('status')
            ->withTimestamps();
    }

    protected $casts = [
        'startDate' => 'datetime',
        'endDate' => 'datetime',
    ];

    protected $hidden = []; // optionally hide pivot data


}
