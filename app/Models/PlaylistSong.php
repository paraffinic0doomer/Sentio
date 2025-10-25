<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaylistSong extends Model
{
    protected $fillable = ['playlist_id', 'title', 'artist', 'url', 'thumbnail'];

    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }
}
