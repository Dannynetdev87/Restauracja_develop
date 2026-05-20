<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    @vite('resources/css/app.css')
</head>
<body class="login-page">
<!--To pójdzie do main -->
<!-- Wrapper centrujący -->
<div class="login-wrapper">

    <!-- Karta logowania -->
    <div class="login-card">

        <!-- Nagłówek -->
        <div class="login-header">
            <h1 class="login-title">Zaloguj się</h1>
            <p class="login-subtitle">Wprowadź swoje dane, aby uzyskać dostęp.</p>
        </div>

        <!-- Formularz -->
        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf

            <!-- Pole Login -->
            <div class="form-group">
                <label for="login" class="form-label">Adres e-mail / Login</label>
                <input
                    type="text"
                    name="login"
                    id="login"
                    value="{{ old('login') }}"
                    required
                    autofocus
                    class="form-input @error('email') has-error @enderror"
                    placeholder="Wprowadz login"
                >
                @error('email')
                <p class="form-error-msg">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pole Hasło -->
            <div class="form-group">
                <label for="password" class="form-label">Hasło</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    required
                    class="form-input @error('password') has-error @enderror"
                    placeholder="••••••••"
                >
                @error('password')
                <p class="form-error-msg">{{ $message }}</p>
                @enderror
            </div>

            <!-- Akcje dodatkowe -->
            <div class="form-actions">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="remember" class="form-checkbox">
                    <span class="checkbox-label">Zapamiętaj mnie</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="" class="auth-link">Zapomniałeś hasła?</a>
                @endif
            </div>

            <!-- Przycisk Submit -->
            <button type="submit" class="btn-primary">
                Zaloguj
            </button>
        </form>

        <!-- Stopka -->
        @if (Route::has('register'))
            <div class="login-footer">
                Nie masz konta?
                <a href="" class="auth-link">Zarejestruj się</a>
            </div>
        @endif

    </div>
</div>

</body>
</html>
