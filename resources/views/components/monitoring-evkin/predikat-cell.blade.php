{{-- Sel predikat evkin: dipakai berulang di tabel (4x triwulan + 1x predikat
     terkini per baris pegawai), jadi ditarik jadi component biar gak duplikat
     markup + logic tone-nya. --}}
@props(['predikat' => null, 'tonePredikat' => []])

@if ($predikat)
    <x-badge :variant="$tonePredikat[$predikat] ?? 'neutral'">{{ $predikat }}</x-badge>
@else
    <span class="mek-cell-empty">—</span>
@endif