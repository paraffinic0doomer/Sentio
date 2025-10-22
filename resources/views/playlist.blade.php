<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playlists - Sentio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .playlist-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Header */
        .playlist-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            cursor: pointer;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 80px;
            width: 250px;
            height: calc(100vh - 80px);
            background: white;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            padding: 2rem 0;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar li {
            margin: 0.5rem 0;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            color: #667eea;
            border-left: 4px solid #667eea;
            padding-left: calc(1.5rem - 4px);
        }

        .sidebar a i {
            width: 20px;
        }

        /* Main Content */
        .playlist-body {
            display: flex;
            width: 100%;
            margin-top: 80px;
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        .page-title {
            margin-bottom: 2rem;
        }

        .page-title h2 {
            color: white;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .page-title p {
            color: rgba(255, 255, 255, 0.8);
        }

        /* Playlists Grid */
        .playlists-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 2rem;
        }

        /* Playlist Folder Card */
        .playlist-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .playlist-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .playlist-thumbnail {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .playlist-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .playlist-thumbnail-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .playlist-icon {
            font-size: 3rem;
            color: white;
            opacity: 0.8;
        }

        .playlist-info {
            padding: 1.5rem;
        }

        .playlist-name {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .playlist-count {
            font-size: 0.85rem;
            color: #999;
        }

        /* Songs List Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 1rem;
        }

        .modal-header h2 {
            color: #333;
            font-size: 1.5rem;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }

        .close-btn:hover {
            color: #667eea;
        }

        .songs-list {
            list-style: none;
        }

        .song-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.3s;
        }

        .song-item:hover {
            background: #f9f9f9;
        }

        .song-details h4 {
            color: #333;
            font-size: 0.95rem;
            margin-bottom: 0.25rem;
        }

        .song-details p {
            color: #999;
            font-size: 0.8rem;
        }

        .song-actions {
            display: flex;
            gap: 0.5rem;
        }

        .song-actions button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.8rem;
        }

        .play-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .play-btn:hover {
            transform: scale(1.05);
        }

        .delete-btn {
            background: #ff6b6b;
            color: white;
        }

        .delete-btn:hover {
            background: #ff5252;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: white;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 1.1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .playlists-container {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="playlist-layout">
        <!-- Header -->
        <header class="playlist-header">
            <div class="logo">Sentio</div>
            <div class="profile-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
        </header>

        <!-- Sidebar -->
        <aside class="sidebar">
            <ul>
                <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="{{ route('playlists.index') }}" class="active"><i class="fas fa-list"></i> Playlists</a></li>
                <li><a href="#"><i class="fas fa-smile"></i> Moods</a></li>
                <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </aside>

        <!-- Main Content -->
        <div class="playlist-body">
            <main class="main-content">
                <!-- Page Title -->
                <div class="page-title">
                    <h2>My Playlists</h2>
                    <p>Organize and enjoy your favorite music collections</p>
                </div>

                <!-- Playlists Grid -->
                @if($playlists->count() > 0)
                    <div class="playlists-container">
                        @foreach($playlists as $playlist)
                            <div class="playlist-card" onclick="openPlaylist({{ $playlist->id }}, '{{ $playlist->name }}')">
                                <!-- Thumbnail -->
                                <div class="playlist-thumbnail">
                                    @if($playlist->songs->first() && $playlist->songs->first()->thumbnail)
                                        <img src="{{ $playlist->songs->first()->thumbnail }}" alt="{{ $playlist->name }}">
                                        <div class="playlist-thumbnail-overlay"></div>
                                    @else
                                        <div class="playlist-icon">🎵</div>
                                    @endif
                                </div>

                                <!-- Info -->
                                <div class="playlist-info">
                                    <div class="playlist-name">{{ $playlist->name }}</div>
                                    <div class="playlist-count">
                                        {{ $playlist->songs->count() }} song{{ $playlist->songs->count() !== 1 ? 's' : '' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-music"></i>
                        <p>No playlists yet. Create one by adding songs from recommendations!</p>
                    </div>
                @endif
            </main>
        </div>
    </div>

    <!-- Songs Modal -->
    <div id="songsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="playlistTitle"></h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <ul class="songs-list" id="songsList">
                <!-- Songs will be loaded here -->
            </ul>
        </div>
    </div>

    <script>
        function openPlaylist(playlistId, playlistName) {
            fetch(`/playlists/${playlistId}/songs`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Songs data:', data);
                document.getElementById('playlistTitle').textContent = playlistName;
                const songsList = document.getElementById('songsList');
                songsList.innerHTML = '';

                if (data.length === 0) {
                    songsList.innerHTML = '<li style="text-align: center; padding: 2rem; color: #999;">No songs in this playlist</li>';
                } else {
                    data.forEach(song => {
                        const li = document.createElement('li');
                        li.className = 'song-item';
                        li.innerHTML = `
                            <div class="song-details">
                                <h4>${song.title}</h4>
                                <p>${song.artist}</p>
                            </div>
                            <div class="song-actions">
                                <button class="play-btn" onclick="playSong('${song.url}')"><i class="fas fa-play"></i> Play</button>
                                <button class="delete-btn" onclick="deleteSong(${song.id})"><i class="fas fa-trash"></i></button>
                            </div>
                        `;
                        songsList.appendChild(li);
                    });
                }

                document.getElementById('songsModal').style.display = 'block';
            })
            .catch(error => console.error('Error:', error));
        }

        function closeModal() {
            document.getElementById('songsModal').style.display = 'none';
        }

        function playSong(url) {
            window.open(url, '_blank');
        }

        function deleteSong(songId) {
            if (confirm('Delete this song from playlist?')) {
                fetch(`/songs/${songId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    location.reload();
                })
                .catch(error => console.error('Error:', error));
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('songsModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>