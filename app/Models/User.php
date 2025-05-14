<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;



class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_photo',
        'is_active',
    ];

    protected $appends = ['user_photo_url'];

    public function getUserPhotoUrlAttribute()
    {
        return $this->user_photo ? asset('storage/' . $this->user_photo) : null;
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $dates = ['deleted_at'];


    public function events()
    {
        return $this->hasMany(Event::class);
    }
    public function likedEvents()
    {
        return $this->belongsToMany(Event::class, 'likes', 'user_id', 'event_id')->withTimestamps();
    }

    public function paidEvents()
    {
        return $this->belongsToMany(Event::class, 'event_user_payments')
            ->withPivot('status')
            ->wherePivot('status', 'YES')
            ->withTimestamps();
    }
}
