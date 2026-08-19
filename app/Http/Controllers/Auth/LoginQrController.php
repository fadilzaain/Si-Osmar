<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginQrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginQrController extends Controller
{
    public function __construct(private LoginQrService $loginQrService) {}

    // Halaman "QR Login Saya" 
    public function show()
    {
        return view('auth.qr-login', [
            'hasActiveToken' => $this->loginQrService->activeTokenExists(auth()->user()),
        ]);
    }

    // Generate/regenerate QR
    public function generate(Request $request)
    {
        $rawToken = $this->loginQrService->generate($request->user());

        return back()->with('freshQrToken', $rawToken);
    }

    public function revoke(Request $request)
    {
        $this->loginQrService->revokeActiveToken($request->user());

        return back()->with('status', 'QR login berhasil dinonaktifkan.');
    }

    // Endpoint tujuan pas QR di-scan. Sengaja di luar middleware 'guest'/'auth'
    // karena harus bisa diakses baik dari device yang belum maupun udah login.
    public function redeem(string $token)
    {
        $user = $this->loginQrService->resolveUser($token);

        if (! $user) {
            Log::warning('Percobaan QR login gagal (token invalid/revoked).', [
                'ip' => request()->ip(),
            ]);

            abort(404);
        }

        Auth::login($user);
        $request = request();
        $request->session()->regenerate();

        Log::info('QR login berhasil.', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard'));
    }
}