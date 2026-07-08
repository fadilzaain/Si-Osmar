<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Pegawai RSUD Jombang
        $pegawaisData = [
            // DOKTER
            [
                'nip' => '198204152010011002',
                'nama' => 'dr. Eko Wahyudi, Sp.PD',
                'jabatan' => 'Dokter Spesialis Penyakit Dalam',
                'unit_kerja' => 'Poli Penyakit Dalam',
                'status_kepegawaian' => 'PNS',
                'email' => 'eko.wahyudi@jombangkab.go.id',
                'telepon' => '081234567801',
                'tanggal_masuk' => '2010-01-15',
                'skor_kompetensi' => 92,
                'status' => 'Aktif',
            ],
            [
                'nip' => '198511202014022001',
                'nama' => 'dr. Rina Lestari, Sp.A',
                'jabatan' => 'Dokter Spesialis Anak',
                'unit_kerja' => 'Poli Anak',
                'status_kepegawaian' => 'PNS',
                'email' => 'rina.lestari@jombangkab.go.id',
                'telepon' => '081234567802',
                'tanggal_masuk' => '2014-02-10',
                'skor_kompetensi' => 95,
                'status' => 'Aktif',
            ],
            [
                'nip' => '198908052018011003',
                'nama' => 'dr. Adi Wijaya',
                'jabatan' => 'Dokter Umum',
                'unit_kerja' => 'IGD',
                'status_kepegawaian' => 'PPPK',
                'email' => 'adi.wijaya@gmail.com',
                'telepon' => '081234567803',
                'tanggal_masuk' => '2018-01-01',
                'skor_kompetensi' => 88,
                'status' => 'Aktif',
            ],
            [
                'nip' => '199103122020122004',
                'nama' => 'dr. Fitriani Indah',
                'jabatan' => 'Dokter Umum',
                'unit_kerja' => 'IGD',
                'status_kepegawaian' => 'PPPK',
                'email' => 'fitriani.indah@outlook.com',
                'telepon' => '081234567804',
                'tanggal_masuk' => '2020-12-01',
                'skor_kompetensi' => 85,
                'status' => 'Aktif',
            ],
            [
                'nip' => null,
                'nama' => 'dr. Budi Santoso, Sp.B',
                'jabatan' => 'Dokter Spesialis Bedah',
                'unit_kerja' => 'Kamar Operasi (OK)',
                'status_kepegawaian' => 'Non-ASN',
                'email' => 'budi.bedah@gmail.com',
                'telepon' => '081234567805',
                'tanggal_masuk' => '2021-06-15',
                'skor_kompetensi' => 90,
                'status' => 'Aktif',
            ],

            // PERAWAT & BIDAN
            [
                'nip' => '198807122015031001',
                'nama' => 'Ns. Ahmad Fauzi, S.Kep',
                'jabatan' => 'Perawat Penyelia',
                'unit_kerja' => 'ICU',
                'status_kepegawaian' => 'PNS',
                'email' => 'ahmad.fauzi@jombangkab.go.id',
                'telepon' => '081234567806',
                'tanggal_masuk' => '2015-03-01',
                'skor_kompetensi' => 89,
                'status' => 'Aktif',
            ],
            [
                'nip' => '199205182019042002',
                'nama' => 'Ns. Siti Aminah, S.Kep',
                'jabatan' => 'Perawat Pelaksana',
                'unit_kerja' => 'ICU',
                'status_kepegawaian' => 'PPPK',
                'email' => 'siti.aminah@yahoo.com',
                'telepon' => '081234567807',
                'tanggal_masuk' => '2019-04-12',
                'skor_kompetensi' => 87,
                'status' => 'Aktif',
            ],
            [
                'nip' => '199009242017082003',
                'nama' => 'Siti Rahayu, A.Md.Keb',
                'jabatan' => 'Bidan Pelaksana',
                'unit_kerja' => 'Kebidanan & Kandungan',
                'status_kepegawaian' => 'PNS',
                'email' => 'siti.rahayu@jombangkab.go.id',
                'telepon' => '081234567808',
                'tanggal_masuk' => '2017-08-01',
                'skor_kompetensi' => 86,
                'status' => 'Aktif',
            ],
            [
                'nip' => null,
                'nama' => 'Dewi Lestari, A.Md.Kep',
                'jabatan' => 'Perawat Pelaksana',
                'unit_kerja' => 'Rawat Jalan',
                'status_kepegawaian' => 'Non-ASN',
                'email' => 'dewi.lestari@gmail.com',
                'telepon' => '081234567809',
                'tanggal_masuk' => '2022-03-01',
                'skor_kompetensi' => 80,
                'status' => 'Aktif',
            ],
            [
                'nip' => null,
                'nama' => 'Bambang Sugeng, A.Md.Kep',
                'jabatan' => 'Perawat Pelaksana',
                'unit_kerja' => 'IGD',
                'status_kepegawaian' => 'Non-ASN',
                'email' => 'bambang.sugeng@gmail.com',
                'telepon' => '081234567810',
                'tanggal_masuk' => '2022-05-10',
                'skor_kompetensi' => 82,
                'status' => 'Cuti',
            ],

            // PENUNJANG MEDIS
            [
                'nip' => '198701042012121002',
                'nama' => 'Hendra Wijaya, A.Md.AK',
                'jabatan' => 'Pranata Laboratorium Kesehatan',
                'unit_kerja' => 'Laboratorium',
                'status_kepegawaian' => 'PNS',
                'email' => 'hendra.wijaya@jombangkab.go.id',
                'telepon' => '081234567811',
                'tanggal_masuk' => '2012-12-01',
                'skor_kompetensi' => 88,
                'status' => 'Aktif',
            ],
            [
                'nip' => '199302142021032001',
                'nama' => 'Apoteker Laila Sari, S.Farm',
                'jabatan' => 'Apoteker Ahli Pertama',
                'unit_kerja' => 'Farmasi',
                'status_kepegawaian' => 'PPPK',
                'email' => 'laila.sari@gmail.com',
                'telepon' => '081234567812',
                'tanggal_masuk' => '2021-03-01',
                'skor_kompetensi' => 91,
                'status' => 'Aktif',
            ],
            [
                'nip' => null,
                'nama' => 'Rahmat Hidayat, A.Md.Rad',
                'jabatan' => 'Radiografer',
                'unit_kerja' => 'Radiologi',
                'status_kepegawaian' => 'Non-ASN',
                'email' => 'rahmat.rad@gmail.com',
                'telepon' => '081234567813',
                'tanggal_masuk' => '2023-01-15',
                'skor_kompetensi' => 78,
                'status' => 'Aktif',
            ],

            // ADMINISTRASI / SDM
            [
                'nip' => '198105242008011003',
                'nama' => 'Sugeng Prastyo, S.Sos',
                'jabatan' => 'Kepala Sub Bagian Kepegawaian',
                'unit_kerja' => 'Administrasi SDM',
                'status_kepegawaian' => 'PNS',
                'email' => 'sugeng.pras@jombangkab.go.id',
                'telepon' => '081234567814',
                'tanggal_masuk' => '2008-01-01',
                'skor_kompetensi' => 87,
                'status' => 'Aktif',
            ],
            [
                'nip' => '199507112022022005',
                'nama' => 'Mega Utami, S.Kom',
                'jabatan' => 'Pranata Komputer',
                'unit_kerja' => 'Administrasi SDM',
                'status_kepegawaian' => 'PPPK',
                'email' => 'mega.utami@gmail.com',
                'telepon' => '081234567815',
                'tanggal_masuk' => '2022-02-01',
                'skor_kompetensi' => 84,
                'status' => 'Aktif',
            ],
            [
                'nip' => null,
                'nama' => 'Dwi Cahyo, A.Md.Kom',
                'jabatan' => 'Staf Administrasi',
                'unit_kerja' => 'Administrasi SDM',
                'status_kepegawaian' => 'Non-ASN',
                'email' => 'dwi.cahyo@gmail.com',
                'telepon' => '081234567816',
                'tanggal_masuk' => '2023-05-01',
                'skor_kompetensi' => 76,
                'status' => 'Nonaktif',
            ]
        ];

        $pegawais = [];
        foreach ($pegawaisData as $data) {
            $pegawais[] = \App\Models\Pegawai::create($data);
        }

        // 2. Data Pelatihan
        $pelatihansData = [
            [
                'nama_pelatihan' => 'Pelatihan BTCLS (Basic Trauma Cardiac Life Support)',
                'penyelenggara' => 'Pusdiklat Kemenkes RI',
                'tanggal_mulai' => '2026-02-10',
                'tanggal_selesai' => '2026-02-15',
                'kuota' => 20,
                'status' => 'Selesai',
            ],
            [
                'nama_pelatihan' => 'Pencegahan & Pengendalian Infeksi (PPI) Dasar',
                'penyelenggara' => 'Komite PPI RSUD Jombang',
                'tanggal_mulai' => '2026-05-18',
                'tanggal_selesai' => '2026-05-20',
                'kuota' => 40,
                'status' => 'Selesai',
            ],
            [
                'nama_pelatihan' => 'Sertifikasi ACLS (Advanced Cardiac Life Support)',
                'penyelenggara' => 'PERKI (Persatuan Dokter Spesialis Kardiovaskular Indonesia)',
                'tanggal_mulai' => '2026-07-10',
                'tanggal_selesai' => '2026-07-14',
                'kuota' => 15,
                'status' => 'Terjadwal',
            ],
            [
                'nama_pelatihan' => 'Pelatihan Pelayanan Prima (Service Excellence)',
                'penyelenggara' => 'Bagian Humas & Pemasaran RSUD Jombang',
                'tanggal_mulai' => '2026-08-05',
                'tanggal_selesai' => '2026-08-07',
                'kuota' => 50,
                'status' => 'Terjadwal',
            ],
            [
                'nama_pelatihan' => 'Sosialisasi SIMRS Modul Kepegawaian (SI-OSMAR)',
                'penyelenggara' => 'Instalasi TI RSUD Jombang',
                'tanggal_mulai' => '2026-06-25',
                'tanggal_selesai' => '2026-06-26',
                'kuota' => 30,
                'status' => 'Selesai',
            ],
        ];

        $pelatihans = [];
        foreach ($pelatihansData as $data) {
            $pelatihans[] = \App\Models\Pelatihan::create($data);
        }

        // 3. Hubungkan Pegawai ke Pelatihan (Pivot)
        // Pelatihan BTCLS (Selesai): Perawat
        $pegawais[5]->pelatihans()->attach($pelatihans[0]->id, ['status' => 'Lulus']); // Ahmad Fauzi
        $pegawais[6]->pelatihans()->attach($pelatihans[0]->id, ['status' => 'Lulus']); // Siti Aminah
        $pegawais[9]->pelatihans()->attach($pelatihans[0]->id, ['status' => 'Lulus']); // Bambang Sugeng

        // Pelatihan PPI (Selesai): Hampir semua medis
        $pegawais[0]->pelatihans()->attach($pelatihans[1]->id, ['status' => 'Lulus']);
        $pegawais[1]->pelatihans()->attach($pelatihans[1]->id, ['status' => 'Lulus']);
        $pegawais[2]->pelatihans()->attach($pelatihans[1]->id, ['status' => 'Lulus']);
        $pegawais[5]->pelatihans()->attach($pelatihans[1]->id, ['status' => 'Lulus']);
        $pegawais[7]->pelatihans()->attach($pelatihans[1]->id, ['status' => 'Lulus']);

        // Pelatihan ACLS (Terjadwal): Dokter
        $pegawais[2]->pelatihans()->attach($pelatihans[2]->id, ['status' => 'Terdaftar']); // Adi Wijaya
        $pegawais[3]->pelatihans()->attach($pelatihans[2]->id, ['status' => 'Terdaftar']); // Fitriani Indah

        // Pelatihan SI-OSMAR (Selesai): Admin & Pimpinan
        $pegawais[13]->pelatihans()->attach($pelatihans[4]->id, ['status' => 'Lulus']); // Sugeng
        $pegawais[14]->pelatihans()->attach($pelatihans[4]->id, ['status' => 'Lulus']); // Mega

    }
}
