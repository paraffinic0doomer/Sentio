<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Sentio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                    <h3>Search Results for "{{ $query }}"</h3>
                    <p>Found {{ $results->count() }} results.</p>
                </div>

                <div class="search-results-list">
                    @forelse($results as $song)
                        <div class="result-item" onclick="playSong('{{ $song['id'] }}')">
                            <div class="result-thumbnail">
                                <img src="{{ $song['thumbnail'] }}" alt="Thumbnail" onerror="this.src='https://via.placeholder.com/120x90?text=No+Image'">
                            </div>
                            <div class="result-details">
                                <h4>{{ $song['title'] }}</h4>
                                <p class="result-artist">{{ $song['artist'] }}</p>
                                <p class="result-meta">{{ number_format($song['views']) }} views • {{ gmdate('i:s', $song['duration']) }}</p>
                            </div>
                        </div>
                    @empty
                        <p>No results found.</p>
                    @endforelse
                </div>
            </main>
        </div>
    </div>

    <script>
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