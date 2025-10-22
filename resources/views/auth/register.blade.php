<!-- filepath: /opt/lampp/htdocs/Sentio/resources/views/auth/register.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — Sentio</title>
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <header class="navbar">
        <div class="logo">🎧 Sentio</div>
        <nav>
            <a href="{{ route('welcome') }}">Home</a>
            <a href="{{ route('login') }}">Login</a>
        </nav>
    </header>

    <!-- Register Section -->
    <section class="section register">
        <div class="register-content fade-in">
            <h1>Create Your Account</h1>
            <p class="caption">Join the Sentio community and discover music that perfectly matches your emotions. Sign up now to start your personalized music journey!</p>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    @error('email') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    @error('password') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                    @error('password_confirmation') <span class="error">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn">Register</button>
            </form>
            <p>Already have an account? <a href="{{ route('login') }}">Login</a></p>
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
</body>
</html>
