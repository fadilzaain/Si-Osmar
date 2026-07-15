@props(['dokumen'])

<div class="mds-doc-cell">
    <x-badge :variant="$dokumen['status']">{{ $dokumen['masa_berlaku'] }}</x-badge>

    @if ($dokumen['file_url'])
        <a href="{{ $dokumen['file_url'] }}" target="_blank" rel="noopener" class="mds-doc-file" title="Lihat berkas">
            <i class="fa-solid fa-file-pdf"></i>
            @if ($dokumen['file_verified'] === true)
                <i class="fa-solid fa-circle-check mds-doc-verified is-ok" title="Terverifikasi"></i>
            @elseif ($dokumen['file_verified'] === false)
                <i class="fa-solid fa-circle-xmark mds-doc-verified is-bad" title="Ditolak"></i>
            @else
                <i class="fa-solid fa-circle-question mds-doc-verified is-pending" title="Belum diverifikasi"></i>
            @endif
        </a>
    @endif
</div>