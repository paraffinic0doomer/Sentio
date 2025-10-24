<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Player - Sentio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .player-layout {
            background: linear-gradient(135deg, #0f0f23, #1a1a2e, #16213e);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .player-header {
            background: rgba(10, 15, 26, 0.9);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #ffffff;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .player-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 2rem;
        }

        .now-playing {
            text-align: center;
            margin-bottom: 3rem;
        }

        .album-art {
            width: 300px;
            height: 300px;
            border-radius: 20px;
            object-fit: cover;
            margin: 0 auto 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s;
        }

        .album-art.playing {
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .track-info h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .track-info p {
            font-size: 1.2rem;
            color: #b0b3b8;
            margin-bottom: 0;
        }

        .player-controls {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .progress-container {
            width: 100%;
            max-width: 600px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .progress-bar {
            flex: 1;
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            cursor: pointer;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(45deg, #00d4ff, #8e2de2);
            border-radius: 3px;
            width: 0%;
            transition: width 0.1s;
        }

        .time-display {
            font-size: 0.9rem;
            color: #b0b3b8;
            min-width: 40px;
        }

        .control-buttons {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .control-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #ffffff;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .control-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .control-btn.play-pause {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #00d4ff, #8e2de2);
        }

        .control-btn.play-pause:hover {
            background: linear-gradient(45deg, #00ff99, #00d4ff);
            box-shadow: 0 10px 30px rgba(0, 212, 255, 0.4);
        }

        .volume-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .volume-bar {
            width: 150px;
            height: 4px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            cursor: pointer;
            position: relative;
        }

        .volume-fill {
            height: 100%;
            background: #ffffff;
            border-radius: 2px;
            width: 70%;
        }

        .playlist-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .playlist-section h3 {
            color: #ffffff;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .playlist-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: background 0.3s;
        }

        .playlist-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .playlist-item:last-child {
            border-bottom: none;
        }

        .playlist-thumb {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 1rem;
        }

        .playlist-info h4 {
            color: #ffffff;
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }

        .playlist-info p {
            color: #b0b3b8;
            margin: 0;
            font-size: 0.9rem;
        }

        .playlist-item.playing {
            background: rgba(0, 212, 255, 0.2);
        }

        .playlist-item.playing h4 {
            color: #00d4ff;
        }

        @media (max-width: 768px) {
            .album-art {
                width: 250px;
                height: 250px;
            }

            .track-info h1 {
                font-size: 2rem;
            }

            .control-buttons {
                gap: 1rem;
            }

            .control-btn {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }

            .control-btn.play-pause {
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="player-layout">
        <!-- Header -->
        <header class="player-header">
            <button class="back-btn" onclick="goBack()">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </button>
            <div class="logo">Sentio Player</div>
            <div></div>
        </header>

        <!-- Main Player -->
        <main class="player-main">
            <div class="now-playing">
                @if($currentSong)
                    <img src="{{ $currentSong->thumbnail ?? asset('images/default-thumb.jpg') }}"
                         alt="Album Art"
                         class="album-art"
                         id="albumArt">
                    <div class="track-info">
                        <h1 id="trackTitle">{{ $currentSong->title }}</h1>
                        <p id="trackArtist">{{ $currentSong->artist }}</p>
                    </div>
                @else
                    <div class="album-art" style="background: linear-gradient(45deg, #8b5cf6, #ec4899); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-music" style="font-size: 4rem; color: white;"></i>
                    </div>
                    <div class="track-info">
                        <h1>Select a song to play</h1>
                        <p>Choose from your playlist below</p>
                    </div>
                @endif
            </div>

            <!-- Player Controls -->
            <div class="player-controls">
                <div class="progress-container">
                    <span class="time-display" id="currentTime">0:00</span>
                    <div class="progress-bar" id="progressBar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    <span class="time-display" id="duration">0:00</span>
                </div>

                <div class="control-buttons">
                    <button class="control-btn" id="prevBtn">
                        <i class="fas fa-step-backward"></i>
                    </button>
                    <button class="control-btn play-pause" id="playPauseBtn">
                        <i class="fas fa-play" id="playPauseIcon"></i>
                    </button>
                    <button class="control-btn" id="nextBtn">
                        <i class="fas fa-step-forward"></i>
                    </button>
                </div>

                <div class="volume-container">
                    <i class="fas fa-volume-up"></i>
                    <div class="volume-bar" id="volumeBar">
                        <div class="volume-fill" id="volumeFill"></div>
                    </div>
                </div>
            </div>

            <!-- Playlist -->
            <div class="playlist-section">
                <h3>Up Next</h3>
                <div id="playlist">
                    @forelse($playlist ?? [] as $song)
                        <div class="playlist-item {{ $currentSong && $currentSong->song_id == $song->song_id ? 'playing' : '' }}"
                             data-song-id="{{ $song->song_id }}"
                             onclick="playSong('{{ $song->song_id }}')">
                            <img src="{{ $song->thumbnail ?? asset('images/default-thumb.jpg') }}"
                                 alt="Thumbnail"
                                 class="playlist-thumb">
                            <div class="playlist-info">
                                <h4>{{ $song->title }}</h4>
                                <p>{{ $song->artist }}</p>
                            </div>
                        </div>
                    @empty
                        <p style="color: #b0b3b8; text-align: center; padding: 2rem;">No songs in your playlist yet.</p>
                    @endforelse
                </div>
            </div>
        </main>
    </div>

    <!-- Audio Element -->
    <audio id="audioPlayer" preload="metadata"></audio>

    <script>
        let currentSongId = '{{ $currentSong ? $currentSong->song_id : "" }}';
        let playlist = @json($playlist ?? []);
        let currentIndex = {{ $currentIndex ?? 0 }};

        const audioPlayer = document.getElementById('audioPlayer');
        const playPauseBtn = document.getElementById('playPauseBtn');
        const playPauseIcon = document.getElementById('playPauseIcon');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');
        const currentTimeDisplay = document.getElementById('currentTime');
        const durationDisplay = document.getElementById('duration');
        const volumeBar = document.getElementById('volumeBar');
        const volumeFill = document.getElementById('volumeFill');
        const albumArt = document.getElementById('albumArt');

        // Initialize player
        if (currentSongId) {
            loadSong(currentSongId);
        }

        // Play/Pause
        playPauseBtn.addEventListener('click', togglePlayPause);

        // Progress bar
        progressBar.addEventListener('click', seek);

        // Volume control
        volumeBar.addEventListener('click', setVolume);

        // Previous/Next
        document.getElementById('prevBtn').addEventListener('click', playPrevious);
        document.getElementById('nextBtn').addEventListener('click', playNext);

        // Audio events
        audioPlayer.addEventListener('loadedmetadata', updateDuration);
        audioPlayer.addEventListener('timeupdate', updateProgress);
        audioPlayer.addEventListener('ended', playNext);

        function loadSong(songId) {
            // Set audio source directly to the stream URL
            audioPlayer.src = `/stream-audio/${songId}`;

            // Update UI immediately
            const song = playlist.find(s => s.song_id === songId);
            if (song) {
                document.getElementById('trackTitle').textContent = song.title;
                document.getElementById('trackArtist').textContent = song.artist;
                if (albumArt) {
                    albumArt.src = song.thumbnail || '/images/default-thumb.jpg';
                }
                updatePlaylistUI(songId);
            }

            // Save to database
            savePlayToDatabase(songId);
        }

        function togglePlayPause() {
            if (audioPlayer.paused) {
                audioPlayer.play();
                playPauseIcon.className = 'fas fa-pause';
                if (albumArt) albumArt.classList.add('playing');
            } else {
                audioPlayer.pause();
                playPauseIcon.className = 'fas fa-play';
                if (albumArt) albumArt.classList.remove('playing');
            }
        }

        function seek(e) {
            const rect = progressBar.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            audioPlayer.currentTime = percent * audioPlayer.duration;
        }

        function setVolume(e) {
            const rect = volumeBar.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            audioPlayer.volume = Math.max(0, Math.min(1, percent));
            volumeFill.style.width = (percent * 100) + '%';
        }

        function updateProgress() {
            const percent = (audioPlayer.currentTime / audioPlayer.duration) * 100;
            progressFill.style.width = percent + '%';
            currentTimeDisplay.textContent = formatTime(audioPlayer.currentTime);
        }

        function updateDuration() {
            durationDisplay.textContent = formatTime(audioPlayer.duration);
        }

        function playSong(songId) {
            currentSongId = songId;
            currentIndex = playlist.findIndex(s => s.song_id === songId);
            loadSong(songId);
            setTimeout(() => audioPlayer.play(), 500); // Small delay to ensure loading
            playPauseIcon.className = 'fas fa-pause';
            if (albumArt) albumArt.classList.add('playing');
        }

        function playPrevious() {
            if (currentIndex > 0) {
                currentIndex--;
                playSong(playlist[currentIndex].song_id);
            }
        }

        function playNext() {
            if (currentIndex < playlist.length - 1) {
                currentIndex++;
                playSong(playlist[currentIndex].song_id);
            }
        }

        function updatePlaylistUI(songId) {
            document.querySelectorAll('.playlist-item').forEach(item => {
                item.classList.remove('playing');
            });
            const currentItem = document.querySelector(`[data-song-id="${songId}"]`);
            if (currentItem) {
                currentItem.classList.add('playing');
            }
        }

        function savePlayToDatabase(songId) {
            const song = playlist.find(s => s.song_id === songId);
            if (song) {
                fetch('/play-song', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        song_id: songId,
                        title: song.title,
                        artist: song.artist,
                        url: song.url,
                        thumbnail: song.thumbnail
                    })
                }).catch(error => console.error('Error saving play:', error));
            }
        }

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        function goBack() {
            // Scroll to bottom of dashboard page
            sessionStorage.setItem('scrollToBottom', 'true');
            window.location.href = '/dashboard';
        }

        // Initialize volume
        audioPlayer.volume = 0.7;
        volumeFill.style.width = '70%';
    </script>
</body>
</html>