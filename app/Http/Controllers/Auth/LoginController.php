<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Auth::attempt otomatis pakai hashing yang sama kayak yang di-set
        // di $casts User model ('password' => 'hashed'), jadi konsisten.
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password yang Anda masukkan salah.',
            ]);
        }

        // WAJIB: regenerate session ID setelah login berhasil.
        // Ini mencegah session fixation attack — kalau nggak di-regenerate,
        // attacker yang tau session ID lama bisa membajak sesi korban.
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        // Invalidate + regenerate token juga wajib pas logout,
        // biar session lama beneran mati total, bukan cuma "logout" di permukaan.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}