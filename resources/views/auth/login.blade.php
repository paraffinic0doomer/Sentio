<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sentio</title>
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <header class="navbar">
        <div class="logo">🎧 Sentio</div>
        <nav>
            <a href="{{ route('welcome') }}">Home</a>
            <a href="{{ route('register') }}">Register</a>
        </nav>
    </header>

    <!-- Login Section -->
    <section class="section login">
        <div class="login-content fade-in">
            <h1>Welcome Back</h1>
            <p class="caption">Log in to your Sentio account and continue discovering music that matches your mood.</p>
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Session Status -->
                @if (session('status'))
                    <div class="error">{{ session('status') }}</div>
                @endif

                <!-- Email Address -->
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                    @error('email') <span class="error">{{ $message }}</span> @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                    @error('password') <span class="error">{{ $message }}</span> @enderror
                </div>

                <!-- Remember Me -->
                <div class="checkbox-group">
                    <input id="remember_me" type="checkbox" name="remember">
                    <label for="remember_me">Remember me</label>
                </div>

                <button type="submit" class="btn">Log In</button>

                <div class="links">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">Forgot your password?</a>
                    @endif
                    <p>Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
                </div>
            </form>
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
