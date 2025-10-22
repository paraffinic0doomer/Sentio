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
                    <li><a href="#"><i class="fas fa-smile"></i> Moods</a></li>
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
                    <button>Get Recommendations</button>
                </div>

                <!-- Recommendations Section -->
                <div class="recommendations" id="recommendations-section" style="display: none;">
                    <h4>Recommendations</h4>
                    <div class="songs-grid" id="recommendations-grid">
                        <!-- LLM-generated recommendations will appear here -->
                    </div>
                </div>

                <!-- Last Played Songs -->
                <div class="last-played">
                    <h4>Last Played</h4>
                    <div class="songs-grid">
                        @forelse($lastPlayed ?? [] as $song)
                            <div class="song-card" onclick="playSong('{{ $song->song_id }}')">
                                <div class="song-icon">♪</div>
                                <div class="song-info">
                                    <h5>{{ $song->title }}</h5>
                                    <p>{{ $song->artist }}</p>
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
        // Get Recommendations
        document.querySelector('.prompt-box button').addEventListener('click', function() {
            const mood = document.querySelector('.prompt-box textarea').value.trim();
            if (mood) {
                fetch('/recommendations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ mood })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Recommendations data:', data);
                    const section = document.getElementById('recommendations-section');
                    const grid = document.getElementById('recommendations-grid');
                    grid.innerHTML = '';

                    data.forEach(song => {
                        const card = document.createElement('div');
                        card.className = 'song-card';
                        card.innerHTML = `
                            <div class="song-icon">♪</div>
                            <div class="song-info">
                                <h5>${song.title}</h5>
                                <p>${song.artist}</p>
                                <p><a href="${song.url}" target="_blank" style="color: #007bff; text-decoration: none; font-size: 12px;">${song.url}</a></p>
                                <button class="add-playlist-btn" onclick="showPlaylistForm('${song.id}', '${song.title}', '${song.artist}', '${song.url}')">➕ Add to Playlist</button>
                            </div>
                        `;
                        grid.appendChild(card);
                    });

                    section.style.display = 'block';
                })
                .catch(error => console.error('Error fetching recommendations:', error));
            } else {
                alert('Please enter a mood to get recommendations.');
            }
        });

        // Add to Playlist
        function showPlaylistForm(songId, title, artist, url) {
            const playlistName = prompt('Enter playlist name:');
            if (playlistName) {
                fetch('/add-to-playlist', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        song_id: songId,
                        title: title,
                        artist: artist,
                        url: url,
                        playlist_name: playlistName
                    })
                })
                .then(response => response.json())
                .then(data => alert(data.message))
                .catch(error => console.error('Error:', error));
            }
        }

        // Play Song from Last Played
        function playSong(songId) {
            fetch('/play-song', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ song_id: songId })
            })
            .then(response => response.json())
            .then(data => {
                window.open(data.url, '_blank');
            });
        }
    </script>
</body>
</html>
