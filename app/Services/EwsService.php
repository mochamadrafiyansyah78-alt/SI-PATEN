<?php

namespace App\Services;

use App\Models\DataAts;
use Carbon\Carbon;

class EwsService
{
    /**
     * Bobot risiko per alasan approval (kolom `alasan_approval`, string bebas
     * dari form Pengawas). Semakin besar, semakin sulit anak kembali
     * bersekolah tanpa intervensi cepat.
     */
    protected array $bobotAlasan = [
        'Mengundurkan Diri'                                            => 20,
        'Menikah / mengurus rumah tangga'                              => 20,
        'Tidak ada biaya'                                              => 15,
        'Bekerja'                                                      => 12,
        'Sekolah jauh dari rumah'                                      => 10,
        'Pengaruh lingkungan/teman'                                    => 12,
        'Tidak mau bersekolah'                                         => 15,
        'Masalah Kesehatan / Penyandang Disabilitas'                   => 10,
        'Sudah cukup dengan tingkat pendidikan yang dimiliki saat ini' => 5,
        'Dikeluarkan'                                                  => 15,
        'Melanjutkan ke Pondok Pesantren atau Lainnya'                 => 5,
        'Lainnya'                                                      => 8,
    ];

    protected array $bobotPrioritas = [
        'Prioritas 1' => 40,
        'Prioritas 2' => 25,
        'Prioritas 3' => 10,
    ];

    /** Daftar alasan baku (dipakai dropdown form agar seragam dengan bobot EWS). */
    public function daftarAlasan(): array
    {
        return array_keys($this->bobotAlasan);
    }

    /**
     * Hitung skor risiko satu record data_ats (0-100).
     */
    public function hitungSkor(DataAts $data): int
    {
        $skor = 0;

        // 1) Status dasar: DO (putus total) lebih berisiko daripada LTM
        $skor += $data->status === 'DO' ? 20 : 10;

        // 2) Prioritas penjangkauan (bobot terbesar, sudah ditentukan Dinas)
        $skor += $this->bobotPrioritas[$data->prioritas_penjangkauan] ?? 10;

        // 3) Alasan approval
        $skor += $this->bobotAlasan[$data->alasan_approval] ?? 8;

        // 4) Faktor "basi" — belum ada progres penanganan sejak diinput
        if ($data->status_penanganan === 'Baru') {
            $hariBerjalan = Carbon::parse($data->created_at)->diffInDays(now());

            if ($hariBerjalan > 30) {
                $skor += 20;
            } elseif ($hariBerjalan > 14) {
                $skor += 10;
            }
        }

        return min($skor, 100);
    }

    public function tentukanLevel(int $skor): string
    {
        return match (true) {
            $skor >= 70 => 'Merah',
            $skor >= 40 => 'Kuning',
            default     => 'Hijau',
        };
    }

    /**
     * Hitung ulang & simpan skor + level untuk satu record.
     * Dipanggil dari DataAtsObserver atau job terjadwal.
     */
    public function refresh(DataAts $data): DataAts
    {
        $skor  = $this->hitungSkor($data);
        $level = $this->tentukanLevel($skor);

        $data->forceFill([
            'ews_score' => $skor,
            'ews_level' => $level,
        ])->saveQuietly(); // saveQuietly agar tidak memicu observer berulang

        return $data;
    }

    /**
     * Hitung ulang seluruh data (dipanggil scheduler harian, karena faktor
     * "hari berjalan" berubah setiap hari walau tidak ada input baru).
     */
    public function refreshAll(): int
    {
        $count = 0;

        DataAts::query()->chunkById(200, function ($chunk) use (&$count) {
            foreach ($chunk as $data) {
                $this->refresh($data);
                $count++;
            }
        });

        return $count;
    }
}
