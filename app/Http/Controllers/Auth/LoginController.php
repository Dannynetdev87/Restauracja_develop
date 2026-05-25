<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $attempt = Auth::attempt(
            [
                'email' => strtolower($credentials['login']),
                'password' => $credentials['password'],
                'is_active' => true,
            ],
            $request->boolean('remember')
        );

        if ($attempt) {
            $request->session()->regenerate();

            return redirect()
                ->intended($this->redirectPathFor($request->user()))
                ->with('success', 'Zalogowano pomyślnie.');
        }

        throw ValidationException::withMessages([
            'login' => 'Podane dane logowania są nieprawidłowe.',
        ]);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Wylogowano pomyślnie.');
    }

    private function redirectPathFor(User $user): string
    {
        return match ($user->role) {
            User::ROLE_ADMIN => route('admin.dashboard'),
            User::ROLE_MANAGER => route('manager.dashboard'),
            User::ROLE_KITCHEN => route('kitchen.current'),
            User::ROLE_BAR => route('bar.current'),
            default => route('waiter.tables.index'),
        };
    }
}
