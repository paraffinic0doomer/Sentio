<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $playlist->name }} - Sentio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="dashboard-layout">
        <header class="dashboard-header">
            <h1><i class="fas fa-music"></i> Sentio</h1>
            <nav>
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <a href="{{ route('search') }}">Search</a>
                <a href="{{ route('playlists.index') }}">Playlists</a>
                <a href="{{ route('profile') }}">Profile</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: #fff; cursor: pointer;">Logout</button>
                </form>
            </nav>
        </header>

        <aside class="sidebar">
            <h3>Navigation</h3>
            <ul>
                <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="{{ route('search') }}"><i class="fas fa-search"></i> Search</a></li>
                <li><a href="{{ route('playlists.index') }}"><i class="fas fa-list"></i> Playlists</a></li>
                <li><a href="{{ route('history') }}"><i class="fas fa-history"></i> History</a></li>
                <li><a href="{{ route('profile') }}"><i class="fas fa-user"></i> Profile</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="welcome-message">
                <h2>{{ $playlist->name }}</h2>
                <p>{{ $playlist->description ?? 'No description' }}</p>
                <small>Created {{ $playlist->created_at->format('M j, Y') }}</small>
            </div>

            @if($songs->count() > 0)
                <div class="songs-grid">
                    @foreach($songs as $song)
                        <div class="song-card">
                            <img src="{{ $song->thumbnail ?? asset('images/cats.jpg') }}" alt="Thumbnail" class="song-thumb" onerror="this.src='{{ asset('images/cats.jpg') }}'">
                            <div class="song-info">
                                <h4>{{ $song->title }}</h4>
                                <p>{{ $song->artist }}</p>
                            </div>
                            <div class="song-actions">
                                <button class="play-btn" onclick="playSong('{{ $song->song_id }}', '{{ addslashes($song->title) }}', '{{ addslashes($song->artist) }}', '{{ $song->thumbnail }}', '{{ $song->url }}')">
                                    <i class="fas fa-play"></i> Play
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-music fa-3x"></i>
                    <h3>No songs in this playlist yet</h3>
                    <p>Add songs from search results or recommendations!</p>
                    <a href="{{ route('search') }}" class="btn">Search for Songs</a>
                </div>
            @endif
        </main>
    </div>

    <script>
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
            }).then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = '/player/playlist/{{ $playlist->id }}/' + songId;
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