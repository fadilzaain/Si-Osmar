<?php

namespace App\Services;

class MonitoringDokumenService
{
    const STATUS_BERLAKU = 'berlaku';
    const STATUS_AKAN_KADALUARSA = 'akan_kadaluarsa';
    const STATUS_KADALUARSA = 'kadaluarsa';
    const STATUS_TIDAK_ADA = 'tidak_ada';

    protected int $ambangHari = 30;

    public function getRingkasanPerRuangan(): array
    {
        return [
            [
                'ruangan' => 'Instalasi Gawat Darurat',
                'profesi' => ['perawat', 'bidan'],
                'total_pegawai' => 12,
                'bermasalah' => 7,
                'breakdown' => ['berlaku' => 5, 'akan_kadaluarsa' => 2, 'kadaluarsa' => 5],
            ],
            [
                'ruangan' => 'Instalasi Laboratorium Patologi Klinik',
                'profesi' => ['analis kesehatan'],
                'total_pegawai' => 19,
                'bermasalah' => 5,
                'breakdown' => ['berlaku' => 12, 'akan_kadaluarsa' => 2, 'kadaluarsa' => 5],
            ],
            [
                'ruangan' => 'Ruang Bersalin',
                'profesi' => ['bidan'],
                'total_pegawai' => 10,
                'bermasalah' => 2,
                'breakdown' => ['berlaku' => 8, 'akan_kadaluarsa' => 1, 'kadaluarsa' => 1],
            ],
        ];
    }

    public function getDetailByRuangan(string $ruangan): array
    {
        $data = [
            'Instalasi Gawat Darurat' => [
                [
                    'nama' => 'Anis Sri Utami, S.Tr.Kes',
                    'jabatan' => 'Perawat',
                    'dokumen' => [
                        'SIP' => ['tgl_berlaku' => null],
                        'SPK' => ['tgl_berlaku' => now()->addDays(20)->toDateString()],
                        'RKK' => ['tgl_berlaku' => now()->addYears(2)->toDateString()],
                    ],
                ],
                [
                    'nama' => 'Dewi Agustina Setyowati, Amd',
                    'jabatan' => 'Perawat',
                    'dokumen' => [
                        'SIP' => ['tgl_berlaku' => null],
                        'SPK' => ['tgl_berlaku' => now()->subDays(10)->toDateString()],
                        'RKK' => ['tgl_berlaku' => now()->subDays(10)->toDateString()],
                    ],
                ],
            ],
        ];

        $pegawai = $data[$ruangan] ?? [];

        foreach ($pegawai as &$p) {
            foreach ($p['dokumen'] as &$d) {
                $d['status'] = $this->resolveStatus($d['tgl_berlaku']);
            }
        }

        return $pegawai;
    }

    public function resolveStatus(?string $tglBerlaku): string
    {
        if (!$tglBerlaku) {
            return self::STATUS_TIDAK_ADA;
        }
        $sisaHari = now()->diffInDays($tglBerlaku, false);
        if ($sisaHari < 0) return self::STATUS_KADALUARSA;
        if ($sisaHari <= $this->ambangHari) return self::STATUS_AKAN_KADALUARSA;
        return self::STATUS_BERLAKU;
    }
}