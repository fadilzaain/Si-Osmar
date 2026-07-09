<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SI-OSMAR</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="auth-shell">
        <div class="auth-brand-panel">
            <div class="auth-brand-content">
                <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo RSUD Jombang" class="auth-brand-logo">
                <h1>SI-OSMAR</h1>
                <p>Sistem Informasi Optimalisasi SDM Rumah Sakit</p>
                <span class="auth-brand-sub">RSUD Jombang</span>
            </div>
        </div>

        <div class="auth-form-panel">
            <div class="auth-form-card">
                <h2>Selamat datang kembali</h2>
                <p class="auth-form-subtitle">Masuk untuk mengakses dashboard SDM</p>

                @if ($errors->any())
                    <div class="auth-error-box">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="auth-field">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@jombangkab.go.id">
                    </div>

                    <div class="auth-field">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="••••••••">
                    </div>

                    <div class="auth-field-row">
                        <label class="auth-checkbox">
                            <input type="checkbox" name="remember">
                            <span>Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit" class="auth-submit-btn">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>