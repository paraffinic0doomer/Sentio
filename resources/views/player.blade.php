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

        .rating-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .rating-stars {
            display: flex;
            gap: 0.5rem;
        }

        .rating-stars i {
            font-size: 1.5rem;
            color: #b0b3b8;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .rating-stars i:hover,
        .rating-stars i.active {
            color: #ffd700;
            transform: scale(1.2);
        }

        .rating-text {
            font-size: 0.9rem;
            color: #9ca3af;
            font-weight: 500;
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

        .additional-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .timestamp-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .timestamp-input {
            width: 60px;
            padding: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 0.9rem;
            text-align: center;
        }

        .timestamp-input::placeholder {
            color: #b0b3b8;
        }

        .timestamp-input:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 0 2px rgba(0, 212, 255, 0.3);
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

            .additional-controls {
                flex-direction: column;
                gap: 0.5rem;
            }

            .timestamp-controls {
                width: 100%;
                justify-content: center;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header h3 {
            margin: 0;
            color: #00d4ff;
            font-size: 1.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            color: #b0b3b8;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .playlist-option {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .playlist-option:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .playlist-option i {
            margin-right: 1rem;
            color: #00d4ff;
        }

        .playlist-option span {
            color: #ffffff;
            font-weight: 500;
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
                    <img src="{{ $currentSong->thumbnail ?? asset('images/cats.jpg') }}"
                         alt="Album Art"
                         class="album-art"
                         id="albumArt">
                    <div class="track-info">
                        <h1 id="trackTitle">{{ $currentSong->title }}</h1>
                        <p id="trackArtist">{{ $currentSong->artist }}</p>
                    </div>

                    <!-- Rating System -->
                    <div class="rating-container">
                        <div class="rating-stars" id="ratingStars">
                            <i class="far fa-star" data-rating="1"></i>
                            <i class="far fa-star" data-rating="2"></i>
                            <i class="far fa-star" data-rating="3"></i>
                            <i class="far fa-star" data-rating="4"></i>
                            <i class="far fa-star" data-rating="5"></i>
                        </div>
                        <span class="rating-text" id="ratingText">Rate this song</span>
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

                <!-- Additional Controls -->
                <div class="additional-controls">
                    <button class="control-btn" id="addToPlaylistBtn" title="Add to Playlist">
                        <i class="fas fa-plus"></i>
                    </button>
                    <div class="timestamp-controls">
                        <input type="text" id="timestampInput" placeholder="0:00" class="timestamp-input">
                        <button class="control-btn" id="seekToTimestampBtn" title="Seek to Time">
                            <i class="fas fa-clock"></i>
                        </button>
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
                            <img src="{{ $song->thumbnail ?? asset('images/cats.jpg') }}"
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

    <!-- Playlist Selection Modal -->
    <div id="playlistModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add to Playlist</h3>
                <button class="modal-close" onclick="closeModal('playlistModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="playlistList">
                    <!-- Playlists will be loaded here -->
                </div>
                <button class="btn btn-secondary" onclick="createNewPlaylist()" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i> Create New Playlist
                </button>
            </div>
        </div>
    </div>

    <!-- Audio Element -->
    <audio id="audioPlayer" preload="metadata"></audio>

    <script>
        let currentSongId = '{{ $currentSong ? $currentSong->song_id : "" }}';
        let playlist = @json($playlist ?? []);
        let currentIndex = {{ $currentIndex ?? 0 }};
        let currentRating = {{ $currentSong ? $currentSong->rating ?? 0 : 0 }};

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
        const ratingStars = document.getElementById('ratingStars');
        const ratingText = document.getElementById('ratingText');

        // Initialize player
        if (currentSongId) {
            loadSong(currentSongId);
        }

        // Initialize rating display
        updateRatingDisplay(currentRating);

        // Play/Pause
        playPauseBtn.addEventListener('click', togglePlayPause);

        // Progress bar
        progressBar.addEventListener('click', seek);

        // Volume control
        volumeBar.addEventListener('click', setVolume);

        // Rating system
        if (ratingStars) {
            ratingStars.addEventListener('click', handleRating);
        }

        // Add to playlist button
        document.getElementById('addToPlaylistBtn').addEventListener('click', openPlaylistModal);

        // Timestamp controls
        document.getElementById('seekToTimestampBtn').addEventListener('click', seekToTimestamp);
        document.getElementById('timestampInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                seekToTimestamp();
            }
        });

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
                    albumArt.src = song.thumbnail || '/images/cats.jpg';
                }
                // Update rating display
                currentRating = song.rating || 0;
                updateRatingDisplay(currentRating);
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

            // Update current rating for the new song
            const song = playlist.find(s => s.song_id === songId);
            currentRating = song ? (song.rating || 0) : 0;
            updateRatingDisplay(currentRating);
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

        function handleRating(e) {
            if (e.target.tagName === 'I') {
                const rating = parseInt(e.target.getAttribute('data-rating'));
                rateSong(rating);
            }
        }

        function rateSong(rating) {
            if (!currentSongId) return;

            fetch('/songs/rate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    song_id: currentSongId,
                    rating: rating
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    currentRating = rating;
                    updateRatingDisplay(rating);

                    // Update the rating in the playlist array
                    const songIndex = playlist.findIndex(s => s.song_id === currentSongId);
                    if (songIndex !== -1) {
                        playlist[songIndex].rating = rating;
                    }
                } else {
                    console.error('Failed to save rating:', data.message);
                }
            })
            .catch(error => {
                console.error('Error rating song:', error);
            });
        }

        function updateRatingDisplay(rating) {
            if (!ratingStars) return;

            const stars = ratingStars.querySelectorAll('i');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.className = 'fas fa-star active';
                } else {
                    star.className = 'far fa-star';
                }
            });

            if (ratingText) {
                if (rating > 0) {
                    ratingText.textContent = `${rating} star${rating > 1 ? 's' : ''}`;
                } else {
                    ratingText.textContent = 'Rate this song';
                }
            }
        }

        function openPlaylistModal() {
            if (!currentSongId) {
                alert('No song is currently playing');
                return;
            }

            // Load user's playlists
            fetch('/playlists/user')
                .then(response => response.json())
                .then(data => {
                    const playlistList = document.getElementById('playlistList');
                    playlistList.innerHTML = '';

                    if (data.length === 0) {
                        playlistList.innerHTML = '<p style="color: #b0b3b8; text-align: center;">No playlists found. Create one first!</p>';
                    } else {
                        data.forEach(playlist => {
                            const option = document.createElement('div');
                            option.className = 'playlist-option';
                            option.onclick = () => addToPlaylist(playlist.id, playlist.name);
                            option.innerHTML = `
                                <i class="fas fa-list"></i>
                                <span>${playlist.name}</span>
                            `;
                            playlistList.appendChild(option);
                        });
                    }

                    openModal('playlistModal');
                })
                .catch(error => {
                    console.error('Error loading playlists:', error);
                    alert('Error loading playlists. Please try again.');
                });
        }

        function addToPlaylist(playlistId, playlistName) {
            if (!currentSongId) return;

            const song = playlist.find(s => s.song_id === currentSongId);
            if (!song) return;

            fetch(`/playlists/${playlistId}/add-song`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    song_id: currentSongId,
                    title: song.title,
                    artist: song.artist,
                    thumbnail: song.thumbnail,
                    url: song.url
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Song added to "${playlistName}" successfully!`);
                    closeModal('playlistModal');
                } else {
                    alert('Error adding song to playlist: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error adding to playlist:', error);
                alert('Error adding song to playlist. Please try again.');
            });
        }

        function createNewPlaylist() {
            const playlistName = prompt('Enter playlist name:');
            if (!playlistName || playlistName.trim() === '') return;

            fetch('/playlists/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    name: playlistName.trim()
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Playlist "${playlistName}" created successfully!`);
                    // Refresh the playlist modal
                    openPlaylistModal();
                } else {
                    alert('Error creating playlist: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error creating playlist:', error);
                alert('Error creating playlist. Please try again.');
            });
        }

        function seekToTimestamp() {
            const input = document.getElementById('timestampInput');
            const timeString = input.value.trim();

            if (!timeString) return;

            const seconds = parseTimestamp(timeString);
            if (seconds !== null && audioPlayer.duration) {
                audioPlayer.currentTime = Math.min(seconds, audioPlayer.duration);
                input.value = ''; // Clear input after seeking
            } else {
                alert('Invalid timestamp format. Use MM:SS or M:SS');
            }
        }

        function parseTimestamp(timeString) {
            // Support formats like "1:30", "01:30", "90" (seconds)
            const parts = timeString.split(':');

            if (parts.length === 1) {
                // Just seconds
                const seconds = parseInt(parts[0]);
                return isNaN(seconds) ? null : seconds;
            } else if (parts.length === 2) {
                // MM:SS format
                const minutes = parseInt(parts[0]);
                const seconds = parseInt(parts[1]);
                if (isNaN(minutes) || isNaN(seconds) || seconds >= 60) {
                    return null;
                }
                return minutes * 60 + seconds;
            }

            return null;
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('show');
            }
        });

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