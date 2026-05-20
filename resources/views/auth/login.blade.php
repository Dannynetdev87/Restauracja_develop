<x-app>
    <x-slot:title>Logowanie - SmakPrzeszłości</x-slot>

    <x-slot:styles>
        @vite(['resources/css/logowanie.css'])
    </x-slot:styles>

    <div class="login-page flex items-center justify-center py-12">
        <div class="login-wrapper">

            <div class="login-card">

                <div class="login-header">
                    <h1 class="login-title">Zaloguj się</h1>
                    <p class="login-subtitle">Wprowadź swoje dane, aby uzyskać dostęp.</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf

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
                            placeholder="Wprowadź login"
                        >
                        @error('email')
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

                        @if (Route::has('password.request'))
                            <a href="" class="auth-link">Zapomniałeś hasła?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-primary">
                        Zaloguj
                    </button>
                </form>

                @if (Route::has('register'))
                    <div class="login-footer">
                        Nie masz konta?
                        <a href="" class="auth-link">Zarejestruj się</a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app>
