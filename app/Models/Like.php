<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Like extends Model
{

    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'event_id'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
