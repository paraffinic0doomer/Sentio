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
                    <li><a href="#"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="#"><i class="fas fa-list"></i> Playlists</a></li>
                    <li><a href="#"><i class="fas fa-smile"></i> Moods</a></li>
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
                                    <small>{{ number_format($song['views'] ?? 0) }} views • {{ formatDuration($song['duration'] ?? 0) }}</small>
                                </div>
                                <div class="result-actions">
                                    <button class="play-btn" onclick="playSong('{{ $song['id'] }}', '{{ addslashes($song['title']) }}', '{{ addslashes($song['artist']) }}', '{{ $song['thumbnail'] }}', '{{ $song['url'] }}')">
                                        <i class="fas fa-play"></i> Play
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
        function formatDuration(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

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
    </script>
</body>
</html>