<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Sekolah;
use App\Services\GuruPiketPopupService;

class AuthController extends Controller
{
    public function showLogin()
    {
        $sekolah = Sekolah::first();
        return view('auth.login', compact('sekolah'));
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $login = $data['login'];
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$field => $login, 'password' => $data['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $request->session()->forget('guru_piket_login_popup');
            $user = Auth::user();
            if ($user instanceof User) {
                $popup = app(GuruPiketPopupService::class)->forLogin($user);
                if ($popup !== null) {
                    $request->session()->put('guru_piket_login_popup', $popup);
                }
            }

            return redirect()->intended(route('home'));
        }

        return back()
            ->withErrors(['login' => 'Email/username atau password salah'])
            ->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
