<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie</title>
    @vite('resources/css/main.css')
    @vite('resources/css/logowanie.css')
</head>
<body class="login-page">
<div class="login-wrapper">

    <div class="login-card">

        <div class="login-header">
            <h1 class="login-title">Zaloguj się</h1>
            <p class="login-subtitle">Wprowadź dane pracownika, aby uzyskać dostęp do panelu.</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf

            <div class="form-group">
                <label for="login" class="form-label">Adres e-mail</label>
                <input
                    type="email"
                    name="login"
                    id="login"
                    value="{{ old('login') }}"
                    required
                    autofocus
                    class="form-input @error('login') has-error @enderror"
                    placeholder="manager@example.com"
                >
                @error('login')
                    <p class="form-error-msg">{{ $message }}</p>
                @enderror
            </div>

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

            <div class="form-actions">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="remember" class="form-checkbox">
                    <span class="checkbox-label">Zapamiętaj mnie</span>
                </label>
            </div>

            <button type="submit" class="btn-primary">
                Zaloguj
            </button>
        </form>

    </div>
</div>

</body>
</html>
