<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sentio — Feel the Music</title>
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <header class="navbar">
        <div class="logo">🎧 Sentio</div>
        <nav>
            <a href="#hero">Home</a>
            <a href="#about">About</a>
            <a href="#features">Features</a>
            <a href="{{ route('login') }}">Login</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="section hero">
        <div class="hero-content fade-in">
            <h1>Let Your Feelings Find Their Sound</h1>
            <p>Type what’s in your heart. Sentio listens — and curates music that feels like you.</p>
            <a href="{{ route('register') }}" class="btn">Get Started</a>
        </div>
        <div class="hero-image fade-up">
            <img src="{{ asset('images/cats.jpg') }}" alt="Sentio mascot enjoying music">
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="section about">
        <div class="about-content fade-in">
            <h2>About Sentio</h2>
            <p>
                Sentio bridges emotion and sound. Using advanced AI, it reads the emotions you express through words and 
                translates them into meaningful playlists. Whether you’re happy, lost, or inspired — Sentio creates the soundtrack of your mood.
            </p>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section features">
        <div class="features-content fade-in">
            <h2>Features</h2>
            <div class="cards">
                <div class="card">
                    <img src="https://cdn-icons-png.flaticon.com/512/727/727245.png" alt="Emotion">
                    <h3>Emotion Analysis</h3>
                    <p>Describe your feelings and let AI understand your emotional depth to suggest matching music.</p>
                </div>
                <div class="card">
                    <img src="https://cdn-icons-png.flaticon.com/512/727/727218.png" alt="Playlist">
                    <h3>Dynamic Playlists</h3>
                    <p>Save your playlists and watch them evolve with your mood and music habits.</p>
                </div>
                <div class="card">
                    <img src="https://cdn-icons-png.flaticon.com/512/727/727240.png" alt="Community">
                    <h3>Emotional Sharing</h3>
                    <p>Share your moods, discover what others feel, and connect through music-driven emotion.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>© {{ date('Y') }} Sentio — Where Emotions Meet Sound</p>
    </footer>

    <!-- Scroll Animations -->
    <script>
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if(entry.isIntersecting){
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.3 });
        document.querySelectorAll('.fade-in, .fade-up').forEach(el => observer.observe(el));
    </script>

    <!-- Card Intersection Observer -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => observer.observe(card));
        });
    </script>
</body>
</html>
