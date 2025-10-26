# Sentio - AI-Powered Music Discovery Platform

Sentio is a modern web application that uses artificial intelligence to help users discover music based on their mood and listening preferences. Built with Laravel and powered by Groq AI, Sentio provides personalized music recommendations through an intuitive and beautiful interface.

## 🌟 Features

### 🎵 Core Functionality
- **Mood-Based Recommendations**: Get personalized song suggestions based on your current mood
- **AI-Powered Discovery**: Uses Groq AI to generate intelligent music recommendations
- **YouTube Integration**: Direct streaming from YouTube with metadata extraction
- **Playlist Management**: Create and manage custom playlists
- **Listening History**: Track your music journey and preferences
- **Explore Mode**: Discover new music with infinite scroll based on mood history

### 🎨 User Experience
- **Modern UI**: Beautiful gradient design with glassmorphism effects
- **Responsive Design**: Works seamlessly on desktop and mobile devices
- **Real-time Search**: Instant song search with live results
- **Intuitive Navigation**: Clean sidebar navigation with active states
- **Dark Theme**: Consistent dark theme optimized for music discovery

### 🔧 Technical Features
- **Session-Based Caching**: Efficient caching of recommendations and mood data
- **Background Processing**: Asynchronous metadata fetching with yt-dlp
- **CSRF Protection**: Secure AJAX requests with Laravel's CSRF tokens
- **Database Optimization**: Efficient queries with proper indexing
- **Error Handling**: Comprehensive error handling and user feedback

## 🚀 Getting Started

### Prerequisites
- PHP 8.1 or higher
- Composer
- Node.js and npm
- MySQL or SQLite database
- yt-dlp (for YouTube metadata extraction)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/sentio.git
   cd sentio
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Setup**
   ```bash
   # Configure your database in .env file
   php artisan migrate
   php artisan db:seed
   ```

6. **Build Assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

7. **Start the Server**
   ```bash
   php artisan serve
   ```

8. **Access the Application**
   Open your browser and navigate to `http://localhost:8000`

## 🔑 Configuration

### Environment Variables

Create a `.env` file in the root directory with the following variables:

```env
APP_NAME=Sentio
APP_ENV=local
APP_KEY=your_app_key_here
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sentio
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

GROQ_API_KEY=your_groq_api_key_here
```

### Groq AI Setup

1. Sign up for a Groq API account at [groq.com](https://groq.com)
2. Generate an API key
3. Add the key to your `.env` file as `GROQ_API_KEY`

## 📁 Project Structure

```
sentio/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php    # Main application logic
│   │   │   ├── InvidiousController.php    # Search functionality
│   │   │   └── PlaylistController.php     # Playlist management
│   │   └── Requests/                      # Form request validation
│   ├── Models/
│   │   ├── User.php                       # User model
│   │   ├── UserSong.php                   # User songs model
│   │   ├── Playlist.php                   # Playlist model
│   │   └── Mood.php                       # User mood tracking
│   ├── Services/
│   │   ├── LlmService.php                 # AI recommendation service
│   │   └── InvidiousService.php           # YouTube integration
│   └── Providers/
├── database/
│   ├── migrations/                        # Database migrations
│   └── seeders/                           # Database seeders
├── public/
│   ├── css/
│   │   └── dashboard.css                  # Main stylesheet
│   ├── js/                                # JavaScript files
│   └── images/                            # Static assets
├── resources/
│   ├── views/                             # Blade templates
│   │   ├── dashboard.blade.php            # Main dashboard
│   │   ├── explore.blade.php              # Explore page
│   │   ├── profile.blade.php              # User profile
│   │   ├── playlist.blade.php             # Playlist management
│   │   └── player.blade.php               # Music player
│   └── css/                               # Additional styles
├── routes/
│   └── web.php                            # Route definitions
└── storage/                               # File storage
```

## 🎯 Key Components

### DashboardController
Handles the main application logic including:
- Mood-based recommendations
- Song playback tracking
- Explore functionality with infinite scroll
- User profile management

### LlmService
AI-powered service that:
- Generates song recommendations using Groq AI
- Fetches YouTube metadata with yt-dlp
- Processes and caches recommendations
- Handles mood-based discovery

### Database Schema
- **users**: User authentication and profiles
- **user_songs**: User's played songs and metadata
- **playlists**: User-created playlists
- **playlist_songs**: Songs within playlists
- **user_moods**: Mood tracking for recommendations

## 🎨 Design System

### Color Palette
- **Primary**: `#00d4ff` (Cyan)
- **Background**: `linear-gradient(135deg, #1a1a2e, #16213e, #0f3460)`
- **Cards**: `rgba(255, 255, 255, 0.1)` with backdrop blur
- **Text**: `#e5e7eb` (Light gray)

### Typography
- **Font Family**: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif
- **Headings**: Various sizes with cyan accents
- **Body Text**: 1.1rem base size for readability

### Components
- **Glassmorphism**: Backdrop blur effects with transparency
- **Gradient Accents**: Cyan gradients for interactive elements
- **Rounded Corners**: 10-15px border radius
- **Smooth Transitions**: 0.3s ease transitions

## 🔧 API Endpoints

### Authentication
- `GET /login` - Login page
- `POST /login` - Process login
- `POST /logout` - Logout user

### Dashboard
- `GET /dashboard` - Main dashboard
- `POST /get-recommendations` - Get mood-based recommendations
- `POST /play-song` - Track song playback

### Explore
- `GET /explore` - Explore page with mood history
- `POST /get-explore-recommendations` - Get paginated recommendations

### Playlists
- `GET /playlists` - List user playlists
- `POST /playlists/create` - Create new playlist
- `POST /add-to-playlist` - Add song to playlist

### Search
- `GET /search` - Search for songs

## 🚀 Deployment

### Production Setup
1. Set `APP_ENV=production` in `.env`
2. Configure production database
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Run `php artisan view:cache`
6. Set up web server (Apache/Nginx) with proper document root

### Environment Requirements
- PHP 8.1+
- MySQL 5.7+ or PostgreSQL
- Composer dependencies
- Node.js for asset compilation
- yt-dlp for metadata extraction

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- **Laravel**: The PHP framework that powers the backend
- **Groq AI**: For providing fast and intelligent AI responses
- **YouTube**: For being an amazing source of music content
- **yt-dlp**: For reliable YouTube metadata extraction
- **Font Awesome**: For beautiful icons
- **Tailwind CSS**: For utility-first CSS inspiration

## 📞 Support

If you encounter any issues or have questions:
1. Check the [Issues](https://github.com/yourusername/sentio/issues) page
2. Create a new issue with detailed information
3. Contact the maintainers

---

**Made with ❤️ and lots of good music**