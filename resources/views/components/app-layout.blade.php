@props(['title' => 'Dashboard'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SI-OSMAR' }} — RSUD Jombang</title>

    {{-- Cegah flash-of-wrong-theme: apply data-theme sebelum CSS di-render --}}
    <script>
        (function () {
            const stored = localStorage.getItem('siosmar-theme') || 'auto';
            const dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const resolved = stored === 'auto' ? (dark ? 'dark' : 'light') : stored;
            document.documentElement.setAttribute('data-theme', resolved);
            document.documentElement.setAttribute('data-theme-mode', stored);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <x-sidebar />

        <div class="app-main">
            <x-navbar :title="$title ?? 'Dashboard'" />

            <main class="app-content">
                {{ $slot }}
            </main>

            <footer class="app-footer">
                &copy; {{ date('Y') }} RSUD Jombang — IT WORKS
            </footer>
        </div>
    </div>
</body>
</html>