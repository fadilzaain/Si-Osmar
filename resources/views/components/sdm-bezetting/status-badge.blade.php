{{-- Badge status bezetting (KURANG/LEBIH/SESUAI): dipakai di badge header
     tiap unit DAN di badge tiap baris jabatan, jadi ditarik jadi component
     biar gak duplikat markup + logic tone/label-nya (sama kayak pola
     <x-monitoring-evkin.predikat-cell>). --}}
@props(['status', 'count' => null])

@php
    $variant = match ($status) {
        'KURANG' => 'danger',
        'LEBIH' => 'info',
        default => 'success',
    };

    $label = match ($status) {
        'KURANG' => 'Kurang' . ($count !== null ? " {$count}" : ''),
        'LEBIH' => 'Lebih' . ($count !== null ? " {$count}" : ''),
        default => 'Sesuai',
    };
@endphp

<x-badge :variant="$variant">{{ $label }}</x-badge>