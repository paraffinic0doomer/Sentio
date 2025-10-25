<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = ['user_id', 'name', 'description'];

    public function songs()
    {
        return $this->hasMany(UserSong::class, 'playlist_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}