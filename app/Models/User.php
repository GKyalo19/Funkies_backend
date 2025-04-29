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
        // 'user_photo',
        // 'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    // public function role()
    // {
    //     return $this->belongsTo(Role::class);
    // }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function isAdmin(): bool
    {
        // //Add debugging
        // \Log::info('isAdmin check:', [
        //     'user_id' => $this->id,
        //     'role_id' => $this->role_id,
        //     'is_admin' => $this->role_id === 1  // or whatever your admin role ID is
        // ]);

        return $this->role_id === 1; // adjust based on your admin role ID
    }

    public function abilities()
    {
        // Implement based on your requirements
        return [
            'view-dashboard' => $this->can('view-dashboard'),
            'manage-users' => $this->can('manage-users'),
            'update-users' => $this->can('update-users', $this),
        ];
    }

    // public function getUserPhotoUrlAttribute()
    // {
    //     return $this->user_photo ? asset('storage/' . $this->user_photo) : null;
    // }
}
