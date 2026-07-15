<x-app-layout :title="$title">
    <div class="cs-wrap" data-aos="fade-up">
        <div class="cs-icon"><i class="{{ $icon }}"></i></div>
        <div class="cs-eyebrow">SI-OSMAR</div>
        <h1 class="cs-title">{{ $title }}</h1>
        <p class="cs-sub">{{ $subtitle }}</p>
        <div class="cs-badge">Modul ini masih dalam pengembangan</div>
        <a href="{{ route('dashboard') }}" class="cs-back">← Kembali ke Dashboard</a>
    </div>
</x-app-layout>