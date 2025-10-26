<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Sentio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .songs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .result-item {
            background: #2a2a2a;
            border-radius: 8px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: background-color 0.2s;
        }

        .result-item:hover {
            background: #3a3a3a;
        }

        .result-thumb {
            width: 60px;
            height: 60px;
            border-radius: 4px;
            object-fit: cover;
        }

        .result-info {
            flex: 1;
            min-width: 0;
        }

        .result-info h4 {
            margin: 0 0 5px 0;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .result-info p {
            margin: 0 0 5px 0;
            color: #b3b3b3;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .result-info small {
            color: #888;
            font-size: 11px;
        }

        .result-actions {
            display: flex;
            gap: 10px;
        }

        .play-btn {
            background: #1db954;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .play-btn:hover {
            background: #1ed760;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }

        .no-results i {
            font-size: 48px;
            margin-bottom: 20px;
            display: block;
        }

        .add-playlist-btn {
            background: #333;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .add-playlist-btn:hover {
            background: #555;
        }

        /* Modal styles */
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #2a2a2a;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 400px;
            color: white;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: white;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #555;
            border-radius: 4px;
            background: #333;
            color: white;
        }

        .modal-content button {
            background: #1db954;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        .modal-content button:hover {
            background: #1ed760;
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Header -->
        <header class="dashboard-header">
            <div class="logo"><a href="{{ route('dashboard') }}">Sentio</a></div>
            <div class="search-container">
                <form action="{{ route('search') }}" method="GET" class="search-form">
                    <div class="search-box">
                        <input type="text" name="q" value="{{ $query }}" placeholder="Search for songs...">
                    </div>
                    <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="profile-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
        </header>

        <!-- Body -->
        <div class="dashboard-body">
            <!-- Sidebar -->
            <aside class="sidebar">
                <ul>
                    <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="{{ route('profile') }}"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="{{ route('playlists.index') }}"><i class="fas fa-list"></i> Playlists</a></li>
                    <li><a href="{{ route('history') }}"><i class="fas fa-history"></i> History</a></li>
                    <li><a href="{{ route('explore') }}"><i class="fas fa-smile"></i> Explore</a></li>
                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <div class="welcome-message">
                    <h3>Search Results @if($query) for "{{ $query }}" @endif</h3>
                    <p>{{ $results->count() }} results found</p>
                </div>

                @if($results->isEmpty())
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h4>No results found</h4>
                        <p>Try searching for a different song or artist</p>
                    </div>
                @else
                    <div class="songs-grid">
                        @foreach($results as $song)
                            <div class="result-item">
                                <img src="{{ $song['thumbnail'] }}" alt="Thumbnail" class="result-thumb" onerror="this.src='{{ asset('images/cats.jpg') }}'">
                                <div class="result-info">
                                    <h4>{{ $song['title'] }}</h4>
                                    <p>{{ $song['artist'] }}</p>
                                    <small>{{ number_format($song['views'] ?? 0) }} views • {{ $song['duration'] }}</small>
                                </div>
                                <div class="result-actions">
                                    <button class="play-btn" onclick="playSong('{{ $song['id'] }}', '{{ addslashes($song['title']) }}', '{{ addslashes($song['artist']) }}', '{{ $song['thumbnail'] }}', '{{ $song['url'] }}')">
                                        <i class="fas fa-play"></i> Play
                                    </button>
                                    <button class="add-playlist-btn" onclick="showPlaylistForm('{{ $song['id'] }}', '{{ addslashes($song['title']) }}', '{{ addslashes($song['artist']) }}', '{{ $song['url'] }}', '{{ $song['thumbnail'] }}')">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </main>
        </div>
    </div>

    <script>
        // Play Song (check if exists, save if not, then redirect to player page)
        function playSong(songId, title, artist, thumbnail, url) {
            console.log('playSong called with:', {songId, title, artist, thumbnail, url});
            
            // First, check if song exists
            fetch('/check-song/' + songId)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Check song failed: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Song exists check:', data);
                if (data.exists) {
                    // Song exists, redirect directly
                    console.log('Song exists, redirecting to player...');
                    window.location.assign('/player/' + songId);
                } else {
                    // Song doesn't exist, save it first
                    console.log('Song does not exist, saving...');
                    return saveSong(songId, title, artist, thumbnail, url);
                }
            })
            .catch(error => {
                console.error('Error checking song:', error);
                alert('Failed to check song. Error: ' + error.message);
            });
        }

        function saveSong(songId, title, artist, thumbnail, url) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                console.error('CSRF token not found!');
                alert('CSRF token missing. Please refresh the page.');
                return;
            }
            
            return fetch('/play-song', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                },
                body: JSON.stringify({
                    song_id: songId,
                    title: title,
                    artist: artist,
                    thumbnail: thumbnail,
                    url: url
                })
            })
            .then(response => {
                console.log('Save response:', response);
                if (!response.ok) {
                    throw new Error('Save failed: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Save data:', data);
                if (data && data.status === 'success') {
                    console.log('Song saved, redirecting to player...');
                    window.location.assign('/player/' + songId);
                } else {
                    throw new Error('Unexpected save response: ' + JSON.stringify(data));
                }
            })
            .catch(error => {
                console.error('Error saving song:', error);
                alert('Failed to save song. Error: ' + error.message);
            });
        }

        function formatDuration(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return mins + ':' + secs.toString().padStart(2, '0');
        }
    </script>

    <!-- Playlist Modal -->
    <div id="playlistModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closePlaylistModal()">&times;</span>
            <h3>Add to Playlist</h3>
            <form id="playlistForm">
                <div class="form-group">
                    <label for="playlistSelect">Select Playlist:</label>
                    <select id="playlistSelect" name="playlist_id">
                        <option value="">Select a playlist...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="newPlaylistName">Or Create New Playlist:</label>
                    <input type="text" id="newPlaylistName" name="name" placeholder="Enter playlist name">
                </div>
                <button type="button" onclick="addToPlaylist()">Add to Playlist</button>
            </form>
        </div>
    </div>

    <script>
        let currentSongData = {};

        function showPlaylistForm(songId, title, artist, url, thumbnail = '') {
            currentSongData = { songId, title, artist, url, thumbnail };

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
                requestData = {
                    song_id: currentSongData.songId,
                    title: currentSongData.title,
                    artist: currentSongData.artist,
                    url: currentSongData.url,
                    thumbnail: currentSongData.thumbnail
                };
                endpoint = `/playlists/${playlistId}/add-song`;
            } else {
                requestData = {
                    name: newPlaylistName,
                    song_id: currentSongData.songId,
                    title: currentSongData.title,
                    artist: currentSongData.artist,
                    url: currentSongData.url,
                    thumbnail: currentSongData.thumbnail
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
                if (data.message) {
                    alert(data.message);
                    closePlaylistModal();
                } else if (data.error) {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error adding to playlist:', error);
                alert('Failed to add song to playlist. Please try again.');
            });
        }

        function closePlaylistModal() {
            document.getElementById('playlistModal').style.display = 'none';
            document.getElementById('playlistForm').reset();
        }
    </script>
</body>
</html>