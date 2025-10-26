<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sentio</title>
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
                    <li><a href="{{ route('dashboard') }}" class="active"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="{{ route('profile') }}"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="{{ route('playlists.index') }}"><i class="fas fa-list"></i> Playlists</a></li>
                    <li><a href="{{ route('history') }}"><i class="fas fa-history"></i> History</a></li>
                    <li><a href="{{ route('explore') }}"><i class="fas fa-compass"></i> Explore</a></li>
                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Welcome Message -->
                <div class="welcome-message">
                    <h3>Welcome back, {{ Auth::user()->name }}!</h3>
                    <p>Ready to discover music that matches your mood? Let's get started.</p>
                </div>

                <!-- Prompt Box -->
                <div class="prompt-box">
                    <h4>How are you feeling today?</h4>
                    <textarea placeholder="Describe your mood or what's on your mind..."></textarea>
                    <button id="get-recommendations-btn">Get Recommendations</button>
                    <div id="loading-spinner" style="display: none; margin-top: 10px;">
                        <i class="fas fa-spinner fa-spin"></i> Getting recommendations...
                    </div>
                </div>

                <!-- Recommendations Section -->
                <div class="recommendations" id="recommendations-section" style="display: none;">
                    <h4>Recommendations</h4>
                    <div class="songs-grid" id="recommendations-grid">
                        <!-- Songs appear here dynamically -->
                    </div>
                </div>

                <!-- Last Played Songs -->
                <div class="last-played">
                    <h4>Last Played</h4>
                    <div class="songs-grid">
                        @forelse($lastPlayed ?? [] as $song)
                            <div class="song-card">
                                <div class="song-thumb">
                                    <img src="{{ $song->thumbnail ?? asset('images/cats.jpg') }}" alt="Thumbnail">
                                </div>
                                <div class="song-info">
                                    <h5>{{ $song->title }}</h5>
                                    <p>{{ $song->artist }}</p>
                                    @if($song->played_at)
                                        <small>{{ \Carbon\Carbon::parse($song->played_at)->diffForHumans() }}</small>
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
                            <p>No songs played yet.</p>
                        @endforelse
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        let currentSongData = null;

        // Track page visibility to detect navigation vs refresh
        let hasNavigatedAway = false;
        let pageLoadTime = Date.now();

        document.addEventListener('DOMContentLoaded', function() {
            // Only load cached recommendations if this is a page refresh (not navigation back)
            // We can detect this by checking if the page loaded very quickly (from cache)
            let timeSinceNavigation = Date.now() - pageLoadTime;
            let wasNavigatedBack = sessionStorage.getItem('hasNavigatedAway') === 'true';

            if (wasNavigatedBack && timeSinceNavigation < 1000) {
                // This was a navigation back, don't load cached recommendations
                sessionStorage.removeItem('hasNavigatedAway');
            } else if (wasNavigatedBack) {
                // This might be a refresh, load cached recommendations
                loadCachedRecommendations();
                sessionStorage.removeItem('hasNavigatedAway');
            }
        });

        // Mark when user navigates away
        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('hasNavigatedAway', 'true');
        });

        function loadCachedRecommendations() {
            fetch('/get-recommendations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.recommendations && data.recommendations.length > 0) {
                    displayRecommendations(data.recommendations, data.feeling, data.cached || false);
                }
            })
            .catch(error => {
                console.log('No cached recommendations found');
            });
        }

        function displayRecommendations(recommendations, feeling, isCached = false) {
            const section = document.getElementById('recommendations-section');
            const grid = document.getElementById('recommendations-grid');

            // Clear existing content
            grid.innerHTML = '';

            // Show section
            section.style.display = 'block';

            // Update header
            const header = section.querySelector('h4');
            header.textContent = `Recommendations for "${feeling}"`;

            recommendations.forEach(song => {
                const songCard = document.createElement('div');
                songCard.className = 'song-card';
                songCard.innerHTML = `
                    <div class="song-thumb">
                        <img src="${song.thumbnail || '/images/cats.jpg'}" alt="Thumbnail" onerror="this.src='/images/cats.jpg'">
                    </div>
                    <div class="song-info">
                        <h5>${song.title}</h5>
                        <p>${song.artist}</p>
                    </div>
                    <div class="song-actions">
                        <button onclick="playSong('${song.id}', '${song.title}', '${song.artist}', '${song.thumbnail || ''}', '${song.url}')" class="play-btn">
                            <i class="fas fa-play"></i> Play
                        </button>
                        <button onclick="showPlaylistForm('${song.id}', '${song.title}', '${song.artist}', '${song.url}', '${song.thumbnail || ''}')" class="add-btn">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                `;
                grid.appendChild(songCard);
            });
        }

        // Get Recommendations
        document.getElementById('get-recommendations-btn').addEventListener('click', function() {
            const mood = document.querySelector('.prompt-box textarea').value.trim();
            if (mood) {
                // Show loading
                document.getElementById('loading-spinner').style.display = 'block';
                this.disabled = true;

                fetch('/get-recommendations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ feeling: mood })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Recommendations data:', data);
                    if (data.status === 'success' && data.recommendations) {
                        displayRecommendations(data.recommendations, data.feeling, data.cached || false);
                    } else {
                        console.error('No recommendations received:', data);
                        alert('Failed to get recommendations. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching recommendations:', error);
                    alert('Failed to get recommendations. Please try again.');
                })
                .finally(() => {
                    // Hide loading
                    document.getElementById('loading-spinner').style.display = 'none';
                    this.disabled = false;
                });
            } else {
                alert('Please enter a mood to get recommendations.');
            }
        });

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

        // Play Song (save to database first, then redirect to player page)
        function playSong(songId, title, artist, thumbnail, url) {
            console.log('playSong called with:', { songId, title, artist, thumbnail, url });

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
                console.log('playSong response:', data);
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
    </script>
</body>
</html>

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

