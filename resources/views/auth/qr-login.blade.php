<x-app-layout title="QR Login Saya">

    <div class="qr-login-page">
        <h1 class="qr-login-title">QR Login Saya</h1>

        <div class="qr-login-warning">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>QR ini setara password siapa pun yang scan bisa langsung masuk Website. Jangan disebar, di-screenshot sembarangan, atau dikirim lewat chat.</span>
        </div>

        <div class="card-base qr-login-card">
            @if (session('freshQrToken'))
                <div class="qr-login-canvas-wrap">
                    <canvas id="qr-login-canvas" data-qr-url="{{ route('login.qr.redeem', session('freshQrToken')) }}"></canvas>
                </div>
                <p class="qr-login-note">
                    Simpan/screenshot QR ini sekarang juga. Setelah halaman di-refresh,
                    QR ini nggak bisa ditampilin ulang -  generate baru.
                </p>
            @else
                <span class="qr-login-status {{ $hasActiveToken ? 'qr-login-status--active' : 'qr-login-status--empty' }}">
                    {{ $hasActiveToken ? 'QR login aktif' : 'Belum ada QR login aktif' }}
                </span>
            @endif

            <div class="qr-login-actions">
                <form method="POST" action="{{ route('profile.qr.generate') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        {{ $hasActiveToken ? 'Regenerate QR' : 'Generate QR Login' }}
                    </button>
                </form>

                @if ($hasActiveToken)
                    <form method="POST" action="{{ route('profile.qr.revoke') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger">Nonaktifkan QR</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

</x-app-layout>