<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SI-OSMAR</title>
    <link rel="preload" href="/fonts/clash-display/ClashDisplay-Semibold.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/general-sans/GeneralSans-Regular.woff2" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/auth.css', 'resources/js/app.js'])
</head>
<body class="auth-body">
    <div class="auth-shell">

        {{-- Panel kiri: brand + animated gradient --}}
        <div class="auth-brand-panel">
            <div class="auth-blob auth-blob--1"></div>
            <div class="auth-blob auth-blob--2"></div>
            <div class="auth-blob auth-blob--3"></div>

            <div class="auth-brand-content">
                <img src="{{ asset('images/logo-rsud-jombang.png') }}" alt="Logo RSUD Jombang" class="auth-brand-logo">
                <h1 class="auth-brand-title">SI-OSMAR</h1>
                <p class="auth-brand-desc">Sistem Informasi Optimalisasi SDM Rumah Sakit</p>
                <div class="auth-brand-divider"></div>
                <span class="auth-brand-sub">RSUD Jombang</span>
            </div>
        </div>

        {{-- Panel kanan: form login --}}
        <div class="auth-form-panel">
            <div class="auth-form-card">
                <h2 class="auth-form-title">Selamat datang kembali</h2>
                <p class="auth-form-subtitle">Masuk untuk mengakses dashboard SDM</p>

                @if ($errors->any())
                    <div class="auth-error-box">
                        <svg class="auth-error-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="auth-form">
                    @csrf

                    <div class="auth-field">
                        <label for="email" class="auth-label">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="auth-input" required autofocus
                               placeholder="nama@jombangkab.go.id">
                    </div>

                    <div class="auth-field">
                        <label for="password" class="auth-label">Password</label>
                        <input type="password" id="password" name="password"
                               class="auth-input" required placeholder="••••••••">
                    </div>

                    <div class="auth-field-row">
                        <label class="auth-checkbox">
                            <input type="checkbox" name="remember">
                            <span class="auth-checkbox-box"></span>
                            <span class="auth-checkbox-label">Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit" class="auth-submit-btn">
                        <span>Masuk</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>