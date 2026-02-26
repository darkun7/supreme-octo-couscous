<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('pages.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            $defaultGame = $user->role === 'super_admin' ? \App\Models\Game::first() : $user->games()->first();
            if ($defaultGame) {
                session(['active_game_id' => $defaultGame->id]);
            }

            return redirect()->intended('/');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function switchGame($id)
    {
        $user = Auth::user();
        $game = \App\Models\Game::findOrFail($id);
        
        if ($user->role === 'super_admin' || $user->games()->where('game_id', $id)->exists()) {
            session(['active_game_id' => $id]);
        }
        
        return redirect()->back();
    }
}
