<?php

namespace App\Services;

use App\Models\LoginQrToken;
use App\Models\User;
use Illuminate\Support\Str;

class LoginQrService
{
    /**
     * Generate token QR baru buat user. Token lama yang masih aktif otomatis
     * di-revoke, jadi satu user cuma punya 1 QR yang valid dalam satu waktu.
     * Return token MENTAH — ini satu-satunya kesempatan buat nampilin ke user,
     * setelah ini yang kesimpen di DB cuma hash-nya.
     */
    public function generate(User $user): string
    {
        $this->revokeActiveToken($user);

        $rawToken = Str::random(64);

        LoginQrToken::create([
            'user_id' => $user->id,
            'token_hash' => $this->hash($rawToken),
        ]);

        return $rawToken;
    }

    public function activeTokenExists(User $user): bool
    {
        return LoginQrToken::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->exists();
    }

    public function revokeActiveToken(User $user): void
    {
        LoginQrToken::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }

    /**
     * Cari user pemilik token QR yang masih aktif, sekaligus catat waktu
     * pemakaian terakhir kmndian return null kalau token nggak valid atauudah di-revoke.
     */
    public function resolveUser(string $rawToken): ?User
    {
        $loginToken = LoginQrToken::with('user')
            ->where('token_hash', $this->hash($rawToken))
            ->whereNull('revoked_at')
            ->first();

        if (! $loginToken) {
            return null;
        }

        $loginToken->update(['last_used_at' => now()]);

        return $loginToken->user;
    }

    private function hash(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }
}