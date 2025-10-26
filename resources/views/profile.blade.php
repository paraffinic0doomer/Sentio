<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Sentio</title>
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
                    <li><a href="{{ route('profile') }}" class="active"><i class="fas fa-user"></i> Profile</a></li>
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
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-avatar-large">
                        <div class="avatar-circle">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="profile-info">
                        <h1>{{ $user->name }}</h1>
                        <p class="profile-email">{{ $user->email }}</p>
                        <p class="profile-join-date">Member since {{ $user->created_at->format('F Y') }}</p>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-music"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ number_format($totalSongs) }}</h3>
                            <p>Total Songs</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ number_format($totalPlaylists) }}</h3>
                            <p>Playlists</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ number_format($user->playlists()->withCount('songs')->get()->sum('songs_count')) }}</h3>
                            <p>Songs in Playlists</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-days"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $user->created_at->diffInDays(now()) }}</h3>
                            <p>Days Active</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="recent-activity">
                    <h3>Recent Activity</h3>
                    <div class="activity-list">
                        @php
                            $recentSongs = \App\Models\UserSong::where('user_id', $user->id)
                                ->whereNotNull('played_at')
                                ->orderBy('played_at', 'desc')
                                ->take(5)
                                ->get();
                        @endphp

                        @if($recentSongs->isNotEmpty())
                            @foreach($recentSongs as $song)
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p class="activity-title">Played "{{ $song->title }}" by {{ $song->artist }}</p>
                                        <p class="activity-time">{{ $song->played_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-music"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="activity-title">No recent activity</p>
                                    <p class="activity-time">Start listening to some music!</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="account-settings">
                    <h3>Account Settings</h3>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <div class="setting-icon">
                                <i class="fas fa-user-edit"></i>
                            </div>
                            <div class="setting-content">
                                <h4>Profile Information</h4>
                                <p>Update your name and email address</p>
                                <button class="btn btn-secondary" onclick="openModal('profileModal')">Edit Profile</button>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <div class="setting-content">
                                <h4>Password & Security</h4>
                                <p>Change your password and security settings</p>
                                <button class="btn btn-secondary" onclick="openModal('passwordModal')">Change Password</button>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="setting-content">
                                <h4>Notifications</h4>
                                <p>Manage your notification preferences</p>
                                <button class="btn btn-secondary" onclick="openModal('notificationsModal')">Manage Notifications</button>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="setting-icon">
                                <i class="fas fa-palette"></i>
                            </div>
                            <div class="setting-content">
                                <h4>Theme & Appearance</h4>
                                <p>Customize the look and feel of Sentio</p>
                                <button class="btn btn-secondary" onclick="openModal('themeModal')">Customize Theme</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <style>
        /* Profile specific styles */
        .profile-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .profile-avatar-large {
            flex-shrink: 0;
        }

        .avatar-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #00d4ff, #0099cc);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
            box-shadow: 0 8px 25px rgba(0, 212, 255, 0.3);
        }

        .profile-info h1 {
            margin: 0 0 0.5rem 0;
            color: #00d4ff;
            font-size: 2rem;
        }

        .profile-email {
            color: #e5e7eb;
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
        }

        .profile-join-date {
            color: #9ca3af;
            margin: 0;
            font-size: 0.9rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            background: linear-gradient(135deg, #00d4ff, #0099cc);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-content h3 {
            margin: 0 0 0.25rem 0;
            font-size: 2rem;
            color: #00d4ff;
        }

        .stat-content p {
            margin: 0;
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .recent-activity,
        .account-settings {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .recent-activity h3,
        .account-settings h3 {
            margin: 0 0 1.5rem 0;
            color: #00d4ff;
            font-size: 1.5rem;
        }

        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        .activity-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: linear-gradient(135deg, #00d4ff, #0099cc);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            margin: 0 0 0.25rem 0;
            color: #e5e7eb;
            font-weight: 500;
        }

        .activity-time {
            margin: 0;
            color: #9ca3af;
            font-size: 0.85rem;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .setting-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            transition: background 0.3s ease;
        }

        .setting-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .setting-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            flex-shrink: 0;
        }

        .setting-content {
            flex: 1;
        }

        .setting-content h4 {
            margin: 0 0 0.5rem 0;
            color: #e5e7eb;
            font-size: 1.1rem;
        }

        .setting-content p {
            margin: 0 0 1rem 0;
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #e5e7eb;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .settings-grid {
                grid-template-columns: 1fr;
            }

            .avatar-circle {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }
        }
    </style>

    <!-- Profile Edit Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Profile Information</h3>
                <span class="modal-close" onclick="closeModal('profileModal')">&times;</span>
            </div>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('profileModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Password Change Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Change Password</h3>
                <span class="modal-close" onclick="closeModal('passwordModal')">&times;</span>
            </div>
            <form action="{{ route('profile.change-password') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('passwordModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notifications Modal -->
    <div id="notificationsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Notification Preferences</h3>
                <span class="modal-close" onclick="closeModal('notificationsModal')">&times;</span>
            </div>
            <form action="{{ route('profile.update-preferences') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="notifications" value="1" {{ ($user->preferences['notifications'] ?? true) ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        Receive email notifications for new recommendations
                    </label>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('notificationsModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Save Preferences</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Theme Modal -->
    <div id="themeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Theme & Appearance</h3>
                <span class="modal-close" onclick="closeModal('themeModal')">&times;</span>
            </div>
            <form action="{{ route('profile.update-preferences') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="theme">Theme</label>
                    <select id="theme" name="theme">
                        <option value="dark" {{ ($user->preferences['theme'] ?? 'dark') == 'dark' ? 'selected' : '' }}>Dark (Default)</option>
                        <option value="light" {{ ($user->preferences['theme'] ?? 'dark') == 'light' ? 'selected' : '' }}>Light</option>
                        <option value="auto" {{ ($user->preferences['theme'] ?? 'dark') == 'auto' ? 'selected' : '' }}>Auto (System)</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('themeModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Save Theme</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Show success/error messages
        @if(session('status'))
            @if(session('status') == 'profile-updated')
                showNotification('Profile updated successfully!', 'success');
            @elseif(session('status') == 'password-changed')
                showNotification('Password changed successfully!', 'success');
            @elseif(session('status') == 'preferences-updated')
                showNotification('Preferences updated successfully!', 'success');
            @endif
        @endif

        @if($errors->any())
            @foreach($errors->all() as $error)
                showNotification('{{ $error }}', 'error');
            @endforeach
        @endif

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;

            // Add to page
            document.body.appendChild(notification);

            // Show notification
            setTimeout(() => notification.classList.add('show'), 100);

            // Hide after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => document.body.removeChild(notification), 300);
            }, 3000);
        }
    </script>

    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            margin: 10% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-header h3 {
            margin: 0;
            color: #00d4ff;
        }

        .modal-close {
            color: #9ca3af;
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .modal-close:hover {
            color: #e5e7eb;
        }

        .form-group {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-group:last-child {
            border-bottom: none;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #e5e7eb;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            color: #e5e7eb;
            font-size: 1rem;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 0 2px rgba(0, 212, 255, 0.2);
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #e5e7eb;
        }

        .checkbox-label input[type="checkbox"] {
            margin-right: 0.5rem;
        }

        .modal-actions {
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .btn-primary {
            background: linear-gradient(135deg, #00d4ff, #0099cc);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 212, 255, 0.3);
        }

        /* Notification Styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 1001;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 400px;
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification.success {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .notification.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
    </style>
</body>
</html>
