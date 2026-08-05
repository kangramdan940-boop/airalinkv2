<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('username', $request->username)
            ->orWhere('email', $request->username)
            ->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Username atau password tidak sesuai.',
            ])->onlyInput('username');
        }

        if (!$user->isActive()) {
            return back()->withErrors([
                'username' => 'Akun belum aktif. Hubungi administrator.',
            ])->onlyInput('username');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'username' => 'Username atau password tidak sesuai.',
            ])->onlyInput('username');
        }

        // Login success
        $user->update(['last_login' => Carbon::now()]);
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        // Redirect berdasarkan permission
        if ($user->can('dashboard-view')) {
            return redirect()->intended('/home');
        }

        return redirect()->intended('/transfer');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
