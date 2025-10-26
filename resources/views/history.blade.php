<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - Sentio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="dashboard-layout">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="logo">Sentio</div>
            <div class="search-container">
                <form action="{{ route('search') }}" method="GET" class="search-form">
                    <div class="search-box">
                        <input type="text" name="q" placeholder="Search for songs...">
                    </div>
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <a href="{{ route('profile') }}" class="profile-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</a>
        </header>

        <!-- Body -->
        <div class="dashboard-body">
            <!-- Sidebar -->
            <aside class="sidebar">
                <ul>
                    <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="{{ route('profile') }}"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="{{ route('playlists.index') }}"><i class="fas fa-list"></i> Playlists</a></li>
                    <li><a href="{{ route('history') }}" class="active"><i class="fas fa-history"></i> History</a></li>
                    <li><a href="{{ route('explore') }}"><i class="fas fa-compass"></i> Explore</a></li>
                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Title -->
                <div class="welcome-message">
                    <h3>Your Listening History</h3>
                    <p>Here are all the songs you've played recently.</p>
                </div>

                <!-- History Section -->
                <div class="last-played">
                    <h4>Recently Played Songs</h4>
                    <div class="songs-grid">
                        @forelse($songs ?? [] as $song)
                            <div class="song-card">
                                <img src="{{ $song->thumbnail ?? asset('images/cats.jpg') }}" alt="Thumbnail" class="song-thumb">
                                <div class="song-info">
                                    <h5>{{ $song->title }}</h5>
                                    <p>{{ $song->artist }}</p>
                                    @if($song->played_at)
                                        <small>Last played {{ \Carbon\Carbon::parse($song->played_at)->diffForHumans() }}</small>
                                    @else
                                        <small>N/A</small>
                                    @endif
                                </div>
                                <div class="song-actions">
                                    <button class="play-btn" onclick="playSong('{{ $song->song_id }}', '{{ addslashes($song->title) }}', '{{ addslashes($song->artist) }}', '{{ $song->thumbnail }}', '{{ $song->url }}')"><i class="fas fa-play"></i> Play</button>
                                    <button class="add-playlist-btn" onclick="showPlaylistForm('{{ $song->song_id }}', '{{ $song->title }}', '{{ $song->artist }}', '{{ $song->url }}')"><i class="fas fa-plus"></i> Add</button>
                                </div>
                            </div>
                        @empty
                            <p>You haven't played any songs yet. Start listening to build your history!</p>
                        @endforelse
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Playlist Modal -->
    <div id="playlistModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add to Playlist</h2>
                <span class="close" onclick="closePlaylistModal()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="playlistSelect">Select existing playlist:</label>
                    <select id="playlistSelect" class="form-control">
                        <option value="">Loading playlists...</option>
                    </select>
                </div>

                <div class="or-divider">
                    <span>OR</span>
                </div>

                <div class="form-group">
                    <label for="newPlaylistName">Create new playlist:</label>
                    <input type="text" id="newPlaylistName" class="form-control" placeholder="Enter playlist name">
                </div>

                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closePlaylistModal()">Cancel</button>
                    <button class="btn btn-primary" onclick="addToPlaylist()">Add to Playlist</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentSongData = null;

        // Play Song (save to database first, then redirect to player page)
        function playSong(songId, title, artist, thumbnail, url) {
            fetch('/play-song', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    song_id: songId,
                    title: title,
                    artist: artist,
                    thumbnail: thumbnail,
                    url: url
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = `/player/${songId}`;
                } else {
                    alert('Failed to play song. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error playing song:', error);
                alert('Failed to play song. Please try again.');
            });
        }

        // Add to Playlist
        function showPlaylistForm(songId, title, artist, url, thumbnail = '') {
            // Store song data
            currentSongData = { songId, title, artist, url, thumbnail };

            // Load user's playlists
            fetch('/playlists/user')
                .then(response => response.json())
                .then(playlists => {
                    const select = document.getElementById('playlistSelect');
                    select.innerHTML = '<option value="">Select a playlist...</option>';

                    playlists.forEach(playlist => {
                        const option = document.createElement('option');
                        option.value = playlist.id;
                        option.textContent = `${playlist.name} (${playlist.songs_count} songs)`;
                        select.appendChild(option);
                    });

                    document.getElementById('playlistModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error loading playlists:', error);
                    alert('Failed to load playlists. Please try again.');
                });
        }

        function addToPlaylist() {
            const playlistId = document.getElementById('playlistSelect').value;
            const newPlaylistName = document.getElementById('newPlaylistName').value.trim();

            if (!playlistId && !newPlaylistName) {
                alert('Please select a playlist or enter a new playlist name.');
                return;
            }

            let requestData;
            let endpoint;

            if (playlistId) {
                // Add to existing playlist
                requestData = {
                    song_id: currentSongData.songId,
                    title: currentSongData.title,
                    artist: currentSongData.artist,
                    url: currentSongData.url,
                    thumbnail: currentSongData.thumbnail,
                    playlist_id: playlistId
                };
                endpoint = '/add-to-playlist';
            } else {
                // Create new playlist and add song
                requestData = {
                    name: newPlaylistName
                };
                endpoint = '/playlists/create';
            }

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                if (playlistId) {
                    alert(data.message);
                } else {
                    // Created new playlist, now add the song to it
                    return fetch('/add-to-playlist', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            song_id: currentSongData.songId,
                            title: currentSongData.title,
                            artist: currentSongData.artist,
                            url: currentSongData.url,
                            thumbnail: currentSongData.thumbnail,
                            playlist_id: data.playlist.id
                        })
                    });
                }
            })
            .then(response => {
                if (response) return response.json();
            })
            .then(data => {
                if (data) alert(data.message);
                closePlaylistModal();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to add song to playlist. Please try again.');
            });
        }

        function closePlaylistModal() {
            document.getElementById('playlistModal').style.display = 'none';
            document.getElementById('newPlaylistName').value = '';
            document.getElementById('playlistSelect').value = '';
        }
    </script>
</body>
</html>
