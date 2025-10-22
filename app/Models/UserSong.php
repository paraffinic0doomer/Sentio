<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSong extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'song_id', 'title', 'artist', 'played_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
