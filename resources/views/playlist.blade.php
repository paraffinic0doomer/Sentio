<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playlists - Sentio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .dashboard-layout {
            display: grid;
            grid-template-columns: 250px 1fr;
            grid-template-rows: auto 1fr;
            height: 100vh;
        }

        .dashboard-header {
            grid-column: 1 / -1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #1e293b;
            padding: 1rem;
            color: #fff;
        }

        .sidebar {
            background-color: #1e293b;
            color: #fff;
            padding: 1rem;
        }

        .main-content {
            padding: 2rem;
            background-color: #0f172a;
            color: #fff;
        }

        .welcome-message {
            margin-bottom: 2rem;
        }

        .songs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .song-card {
            background-color: #1e293b;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            color: #fff;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .song-card:hover {
            transform: translateY(-5px);
        }

        .empty-state {
            text-align: center;
            margin-top: 2rem;
            color: #94a3b8;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .create-playlist-btn {
            background-color: #00d4ff;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .create-playlist-btn:hover {
            background-color: #0099cc;
        }

        .create-playlist-btn i {
            margin-right: 0.5rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            background-color: #1e293b;
            margin: 5% auto;
            padding: 0;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
            color: white;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #334155;
        }

        .modal-header h2 {
            margin: 0;
            color: #ffffff;
        }

        .close {
            color: #94a3b8;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #ffffff;
        }

        .modal-body {
            padding: 1rem 1.5rem;
            max-height: 60vh;
            overflow-y: auto;
        }

        .songs-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .song-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #334155;
        }

        .song-item:last-child {
            border-bottom: none;
        }

        .song-details {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .song-details h4 {
            margin: 0 0 0.25rem 0;
            color: #ffffff;
        }

        .song-details p {
            margin: 0;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .song-actions {
            display: flex;
            gap: 0.5rem;
        }

        .play-btn, .delete-btn {
            background: none;
            border: none;
            color: #94a3b8;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .play-btn:hover {
            color: #00d4ff;
            background: rgba(0, 212, 255, 0.1);
        }

        .delete-btn:hover {
            color: #ff4757;
            background: rgba(255, 71, 87, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #ffffff;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #334155;
            border-radius: 5px;
            background-color: #0f172a;
            color: #ffffff;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #00d4ff;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .cancel-btn, .create-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .cancel-btn {
            background-color: #64748b;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #475569;
        }

        .create-btn {
            background-color: #00d4ff;
            color: white;
        }

        .create-btn:hover {
            background-color: #0099cc;
        }
    </style>
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

        <!-- Sidebar -->
        <aside class="sidebar">
            <ul>
                <li><a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="{{ route('profile') }}"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="{{ route('playlists.index') }}" class="active"><i class="fas fa-list"></i> Playlists</a></li>
                <li><a href="{{ route('history') }}"><i class="fas fa-history"></i> History</a></li>
                <li><a href="#"><i class="fas fa-smile"></i> Moods</a></li>
                <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="welcome-message">
                <h3>My Playlists</h3>
                <p>Organize and enjoy your favorite music collections</p>
                <button onclick="showCreatePlaylistModal()" class="create-playlist-btn">
                    <i class="fas fa-plus"></i> Create New Playlist
                </button>
            </div>

            @if($playlists->count() > 0)
                <div class="songs-grid">
                    @foreach($playlists as $playlist)
                        <div class="song-card" onclick="openPlaylist({{ $playlist->id }}, '{{ $playlist->name }}')">
                            <div class="song-icon">🎵</div>
                            <div class="song-info">
                                <h5>{{ $playlist->name }}</h5>
                                <p>{{ $playlist->songs->count() }} song{{ $playlist->songs->count() !== 1 ? 's' : '' }}</p>
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

    <!-- Playlist Songs Modal -->
    <div id="songsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="playlistTitle">Playlist</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <ul id="songsList" class="songs-list">
                    <!-- Songs will be loaded here -->
                </ul>
            </div>
        </div>
    </div>

    <!-- Create Playlist Modal -->
    <div id="createPlaylistModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create New Playlist</h2>
                <span class="close" onclick="closeCreateModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="createPlaylistForm">
                    <div class="form-group">
                        <label for="playlistName">Playlist Name</label>
                        <input type="text" id="playlistName" name="name" required placeholder="Enter playlist name">
                    </div>
                    <div class="form-actions">
                        <button type="button" onclick="closeCreateModal()" class="cancel-btn">Cancel</button>
                        <button type="submit" class="create-btn">Create Playlist</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openPlaylist(id, name) {
            fetch(`/playlists/${id}/songs`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('playlistTitle').textContent = name;
                    const ul = document.getElementById('songsList');
                    ul.innerHTML = '';
                    if (data.length === 0) {
                        ul.innerHTML = '<li style="text-align:center;color:#555;padding:1rem;">No songs in this playlist</li>';
                    } else {
                        data.forEach(song => {
                            const li = document.createElement('li');
                            li.className = 'song-item';
                            li.innerHTML = `
                                <div class="song-details">
                                    <img src="${song.thumbnail || '/images/cats.jpg'}" alt="Thumbnail" style="width: 50px; height: 50px; border-radius: 5px; margin-right: 1rem; object-fit: cover;">
                                    <div>
                                        <h4>${song.title}</h4>
                                        <p>${song.artist}</p>
                                    </div>
                                </div>
                                <div class="song-actions">
                                    <button class="play-btn" onclick="playSong('${song.song_id}')"><i class="fas fa-play"></i></button>
                                    <button class="delete-btn" onclick="deleteSong(${song.id})"><i class="fas fa-trash"></i></button>
                                </div>
                            `;
                            ul.appendChild(li);
                        });
                    }
                    document.getElementById('songsModal').style.display = 'block';
                })
                .catch(err => console.error(err));
        }

        function closeModal() {
            document.getElementById('songsModal').style.display = 'none';
        }

        function playSong(songId) {
            window.location.href = `/player/${songId}`;
        }

        function deleteSong(id) {
            if (confirm('Delete this song?')) {
                fetch(`/songs/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                }).then(res => res.json()).then(data => {
                    alert(data.message);
                    location.reload();
                });
            }
        }

        window.onclick = function (e) {
            const songsModal = document.getElementById('songsModal');
            const createModal = document.getElementById('createPlaylistModal');
            if (e.target == songsModal) closeModal();
            if (e.target == createModal) closeCreateModal();
        }

        function showCreatePlaylistModal() {
            document.getElementById('createPlaylistModal').style.display = 'block';
            document.getElementById('playlistName').focus();
        }

        function closeCreateModal() {
            document.getElementById('createPlaylistModal').style.display = 'none';
            document.getElementById('createPlaylistForm').reset();
        }

        document.getElementById('createPlaylistForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const name = document.getElementById('playlistName').value.trim();
            
            if (!name) {
                alert('Please enter a playlist name');
                return;
            }

            fetch('/playlists/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: name })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    alert(data.message);
                    closeCreateModal();
                    location.reload(); // Refresh to show new playlist
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error creating playlist');
            });
        });
    </script>
</body>
</html>
