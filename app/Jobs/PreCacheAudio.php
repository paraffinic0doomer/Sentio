<?php

namespace App\Jobs;

use App\Models\UserSong;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;

class PreCacheAudio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $song;

    public function __construct(UserSong $song)
    {
        $this->song = $song;
    }

    public function handle()
    {
        // This job is no longer needed in the reverted version
        // Keeping the file for potential future use
        return;
    }
}
