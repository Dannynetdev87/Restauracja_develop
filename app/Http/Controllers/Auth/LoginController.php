<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Wyświetla widok logowania.
     */
    public function create()
    {
        // Ścieźka do pliku w views
        return view('auth.login');
    }

    /**
     * Obsługuje proces logowania.
     */
    public function store(Request $request)
    {
        // Walidacja danych z formularza
        $credentials = $request->validate([
            'login'    => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Próba logowania (mapujemy pole 'login' z formularza na kolumnę 'email' w bazie)
        $attempt = Auth::attempt(
            [
                'email'    => $credentials['login'],
                'password' => $credentials['password']
            ],
            $request->boolean('remember') // Obsługa "Zapamiętaj mnie"
        );

        // Jeśli logowanie się powiodło
        if ($attempt) {
            // Zabezpieczenie przed atakami Session Fixation
            $request->session()->regenerate();

            // Przekierowanie tam, gdzie użytkownik chciał wejść, lub na domyślny panel
            return redirect()->intended('/dashboard')->with('success', 'Zalogowano pomyślnie!');
        }

        // Jeśli logowanie się nie powiodło (zwracamy błąd pod klucz 'email',
        // bo tak masz ustawione @error('email') w pliku Blade)
        return back()->withErrors([
            'email' => 'Podane dane logowania są nieprawidłowe.',
        ])->onlyInput('login'); // Zostawia wpisany login w formularzu
    }

    /**
     * Wylogowywanie użytkownika.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Wylogowano pomyślnie.');
    }
}
