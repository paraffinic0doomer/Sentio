<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore - Sentio</title>
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
                    <li><a href="{{ route('history') }}"><i class="fas fa-history"></i> History</a></li>
                    <li><a href="{{ route('explore') }}" class="active"><i class="fas fa-compass"></i> Explore</a></li>
                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Explore Header -->
                <div class="welcome-message">
                    <h3>Explore Music Based on Your Mood</h3>
                    @if(isset($hasMoodHistory) && $hasMoodHistory)
                        <p>Discover endless music tailored to how you've been feeling. Your primary mood: <strong>{{ $primaryMood }}</strong></p>
                    @else
                        <p>Get started by going to the <a href="{{ route('dashboard') }}" style="color: #00d4ff;">dashboard</a> and getting some recommendations to build your mood history!</p>
                    @endif
                </div>

                <!-- Mood History -->
                <div class="mood-history">
                    <h4>Your Mood History</h4>
                    @if($recentMoods->isNotEmpty())
                        <div class="mood-tags">
                            @foreach($recentMoods->groupBy('date')->sortKeysDesc() as $date => $moods)
                                <div class="mood-day">
                                    <span class="date">{{ \Carbon\Carbon::parse($date)->format('M j') }}</span>
                                    @php
                                        $allMoodsForDay = [];
                                        foreach($moods as $moodRecord) {
                                            $moodStrings = explode(', ', $moodRecord->mood);
                                            $allMoodsForDay = array_merge($allMoodsForDay, $moodStrings);
                                        }
                                        $uniqueMoodsForDay = array_unique($allMoodsForDay);
                                    @endphp
                                    @foreach($uniqueMoodsForDay as $mood)
                                        <span class="mood">{{ $mood }}</span>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="color: #888; font-style: italic;">No mood history yet. Get recommendations on the dashboard to start building your mood profile!</p>
                    @endif
                </div>

                <!-- Explore Songs -->
                <div class="explore-section">
                    <h4>Discover Songs</h4>
                    <div class="songs-grid" id="explore-grid">
                        <!-- Songs will be loaded here -->
                    </div>
                    <div id="loading-spinner" style="display: none; text-align: center; margin: 20px;">
                        <i class="fas fa-spinner fa-spin"></i> Discovering more songs...
                    </div>
                    <div id="load-more-container" style="text-align: center; margin: 20px; display: none;">
                        <button id="load-more-btn" class="btn btn-primary">Load More Songs</button>
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
        console.log('Explore page script loaded - PAGE LOADED SUCCESSFULLY');
        let currentSongData = null;
        let currentOffset = 0;
        const initialLoad = 10;
        const subsequentLoad = 10;
        let isLoading = false;
        let hasMoreSongs = true;

        // Load initial songs when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadExploreSongs();
        });

        // Also try to load immediately in case DOMContentLoaded doesn't fire
        console.log('Script executing immediately');
        if (document.readyState === 'loading') {
            console.log('Document still loading, waiting for DOMContentLoaded');
        } else {
            console.log('Document already loaded, loading songs immediately');
            loadExploreSongs();
        }

        function loadExploreSongs() {
            console.log('loadExploreSongs called, isLoading:', isLoading, 'hasMoreSongs:', hasMoreSongs);
            if (isLoading || !hasMoreSongs) return;

            isLoading = true;
            document.getElementById('loading-spinner').style.display = 'block';
            document.getElementById('load-more-btn').disabled = true;

            // Use different limit for initial load vs subsequent loads
            const limit = currentOffset === 0 ? initialLoad : subsequentLoad;
            console.log('Making request with offset:', currentOffset, 'limit:', limit);

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            console.log('CSRF token found:', csrfToken ? 'yes' : 'no');

            fetch('/get-explore-recommendations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    offset: currentOffset,
                    limit: limit
                })
            })
            .then(function(response) {
                console.log('Fetch response status:', response.status);
                console.log('Fetch response ok:', response.ok);
                return response.json();
            })
            .then(function(data) {
                console.log('Parsed response data:', data);
                if (data.status === 'success' && data.recommendations) {
                    console.log('Success - displaying', data.recommendations.length, 'songs');
                    displayExploreSongs(data.recommendations);
                    hasMoreSongs = data.hasMore;
                    currentOffset += limit;

                    if (!hasMoreSongs) {
                        document.getElementById('load-more-container').style.display = 'none';
                    } else {
                        document.getElementById('load-more-container').style.display = 'block';
                    }
                } else if (data.status === 'error') {
                    console.log('No mood history:', data.message);
                    hasMoreSongs = false;
                    document.getElementById('load-more-container').style.display = 'none';
                    // Show message in the grid
                    const grid = document.getElementById('explore-grid');
                    grid.innerHTML = '<p style="color: #888; font-style: italic; text-align: center; width: 100%;">No mood history found. Please get recommendations first to start exploring!</p>';
                } else {
                    console.error('Failed to load songs:', data);
                    hasMoreSongs = false;
                    document.getElementById('load-more-container').style.display = 'none';
                }
            })
            .catch(function(error) {
                console.error('Error loading songs:', error);
                hasMoreSongs = false;
                document.getElementById('load-more-container').style.display = 'none';
                const grid = document.getElementById('explore-grid');
                grid.innerHTML = '<p style="color: #888; font-style: italic; text-align: center; width: 100%;">Unable to load songs. Please try again later.</p>';
            })
            .finally(function() {
                isLoading = false;
                document.getElementById('loading-spinner').style.display = 'none';
                document.getElementById('load-more-btn').disabled = false;
                console.log('Request completed, isLoading set to false');
            });
        }

        function displayExploreSongs(songs) {
            const grid = document.getElementById('explore-grid');
            // Clear existing content (including test songs)
            grid.innerHTML = '';

            songs.forEach(function(song) {
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
                        <button onclick="playSong('${song.id}', '${song.title.replace(/'/g, "\\'")}', '${song.artist.replace(/'/g, "\\'")}', '${song.thumbnail || ''}', '${song.url}')" class="play-btn">
                            <i class="fas fa-play"></i> Play
                        </button>
                        <button onclick="showPlaylistForm('${song.id}', '${song.title.replace(/'/g, "\\'")}', '${song.artist.replace(/'/g, "\\'")}', '${song.url}', '${song.thumbnail || ''}')" class="add-btn">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                `;
                grid.appendChild(songCard);
            });
        }

        // Load more songs when button is clicked
        document.getElementById('load-more-btn').addEventListener('click', function() {
            loadExploreSongs();
        });

        // Infinite scroll - load more when user scrolls near bottom
        window.addEventListener('scroll', function() {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
                loadExploreSongs();
            }
        });

        // Add to Playlist
        function showPlaylistForm(songId, title, artist, url, thumbnail = '') {
            // Store song data
            currentSongData = { songId, title, artist, url, thumbnail };

            // Load user's playlists
            fetch('/playlists/user')
                .then(function(response) { return response.json(); })
                .then(function(playlists) {
                    const select = document.getElementById('playlistSelect');
                    select.innerHTML = '<option value="">Select a playlist...</option>';

                    playlists.forEach(function(playlist) {
                        const option = document.createElement('option');
                        option.value = playlist.id;
                        option.textContent = `${playlist.name} (${playlist.songs_count} songs)`;
                        select.appendChild(option);
                    });

                    document.getElementById('playlistModal').style.display = 'block';
                })
                .catch(function(error) {
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
            .then(function(response) {
                console.log('Fetch response received:', response);
                return response.json();
            })
            .then(function(data) {
                console.log('Parsed response data:', data);
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
                            'Content-Type' => 'application/json',
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
            .then(function(response) {
                console.log('Fetch response received:', response);
                return response.json();
            })
            .then(function(data) {
                console.log('Parsed response data:', data);
                if (data.status === 'success') {
                    alert('Song added to playlist successfully!');
                    closePlaylistModal();
                } else {
                    alert('Failed to add song to playlist. Please try again.');
                }
            })
            .catch(function(error) {
                console.error('Error adding to playlist:', error);
                alert('An error occurred while adding to playlist. Please try again.');
            });
        }

        function closePlaylistModal() {
            document.getElementById('playlistModal').style.display = 'none';
            document.getElementById('playlistSelect').value = '';
            document.getElementById('newPlaylistName').value = '';
        }
    </script>
</body>
</html>