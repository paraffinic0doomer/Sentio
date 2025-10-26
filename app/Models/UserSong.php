<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSong extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'playlist_id',
        'song_id',
        'title',
        'artist',
        'played_at',
        'thumbnail',
        'audio_url',
        'audio_extracted_at',
        'url',
        'rating',
    ];

    public $timestamps = true;

    // ✅ These make diffForHumans() and date formatting work without crashing
    protected $casts = [
        'played_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'audio_extracted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
