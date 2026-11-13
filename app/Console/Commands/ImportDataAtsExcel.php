<?php

namespace App\Console\Commands;

use App\Models\DataAts;
use App\Models\Kecamatan;
use App\Models\Sekolah;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportDataAtsExcel extends Command
{
    protected $signature = 'import:data-ats
        {path : Path lengkap ke file .xlsx}
        {--input-by= : ID user (UUID) yang dicatat sebagai pencatat data, default admin_dinas pertama}
        {--dry-run : Hanya simulasi, tidak menyimpan apapun ke database}';

    protected $description = 'Import data ATS dari file Excel "Data ATS Per Kecamatan" (satu sheet per kecamatan)';

    private const SHEET_DIABAIKAN = 'Ringkasan Per Kecamatan';

    /**
     * Peta kecamatan-di-luar-Lebak yang muncul di dataset ke kabupaten aslinya,
     * supaya tampil rapi di UI (bukan cuma "tidak dikenali"). Kalau ada nama
     * kecamatan luar baru yang belum ada di sini, tetap masuk sebagai
     * 'Luar Kab. Lebak' (tidak pernah menggagalkan import).
     */
    private const KABUPATEN_TETANGGA = [
        'cikeusik' => 'Kab. Pandeglang',
        'cibaliung' => 'Kab. Pandeglang',
        'pandeglang' => 'Kab. Pandeglang',
        'koroncong' => 'Kab. Pandeglang',
        'picung' => 'Kab. Pandeglang',
        'mekarjaya' => 'Kab. Pandeglang',
        'kaduhejo' => 'Kab. Pandeglang',
        'tunjungteja' => 'Kab. Serang',
        'cikande' => 'Kab. Serang',
        'solear' => 'Kab. Tangerang',
        'teluknaga' => 'Kab. Tangerang',
    ];

    private array $kecamatanMap = [];

    private int $totalSekolahBaru = 0;

    private int $totalDataBaru = 0;

    private int $totalDilewati = 0;

    private array $errorRows = [];

    private array $konflikKecamatanSekolah = [];

    private array $infoLuarLebak = [];

    private array $sekolahKecamatanTerpakai = [];

    public function handle(): int
    {
        $path = $this->argument('path');

        if (! file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $inputBy = $this->option('input-by')
            ?? User::where('role', 'admin_dinas')->value('id');

        if (! $inputBy) {
            $this->error('Tidak ada user admin_dinas ditemukan untuk dijadikan pencatat (input_by). Buat dulu akun admin, atau gunakan --input-by=<uuid>.');

            return self::FAILURE;
        }

        $this->buildKecamatanMap();

        $this->info('Membaca file: ' . $path);
        $spreadsheet = IOFactory::load($path);

        $existingNik = DataAts::pluck('nik')->flip();

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if ($sheetName === self::SHEET_DIABAIKAN) {
                continue;
            }

            $this->line("--- Memproses sheet: {$sheetName} ---");
            $this->prosesSheet($spreadsheet->getSheetByName($sheetName), $inputBy, $existingNik, $dryRun);
        }

        $this->newLine();
        $this->info("Sekolah baru dibuat : {$this->totalSekolahBaru}");
        $this->info("Data ATS baru       : {$this->totalDataBaru}");
        $this->info("Dilewati (duplikat) : {$this->totalDilewati}");

        if ($this->errorRows) {
            $this->warn(count($this->errorRows) . ' baris dilewati (gagal validasi):');
            foreach (array_slice($this->errorRows, 0, 30) as $err) {
                $this->line("  - {$err}");
            }
            if (count($this->errorRows) > 30) {
                $this->line('  ... dan ' . (count($this->errorRows) - 30) . ' lainnya.');
            }
        }

        if ($this->konflikKecamatanSekolah) {
            $this->newLine();
            $this->warn(count($this->konflikKecamatanSekolah) . ' sekolah punya data kecamatan yang TIDAK KONSISTEN antar baris di file sumber (dipakai kecamatan dari kemunculan pertama, sisanya diabaikan) — mohon cek manual:');
            foreach ($this->konflikKecamatanSekolah as $k) {
                $this->line("  - NPSN {$k['npsn']} ({$k['nama']}): dipakai '{$k['dipakai']}', tapi baris {$k['sheet']}#{$k['row']} bilang '{$k['ditemukan']}'");
            }
        }

        if ($this->infoLuarLebak) {
            $this->newLine();
            $this->info(count($this->infoLuarLebak) . ' sekolah di luar Kabupaten Lebak berhasil diimpor (siswa tetap domisili Lebak):');
            foreach ($this->infoLuarLebak as $i) {
                $this->line("  - {$i}");
            }
        }

        if ($dryRun) {
            $this->warn('Mode --dry-run aktif: TIDAK ADA data yang benar-benar disimpan.');
        }

        return self::SUCCESS;
    }

    private function buildKecamatanMap(): void
    {
        foreach (Kecamatan::all() as $k) {
            $this->kecamatanMap[$this->normalisasiNamaKecamatan($k->nama_kecamatan)] = $k->id;
        }
    }

    private function normalisasiNamaKecamatan(string $nama): string
    {
        $nama = preg_replace('/^kec\.?\s*/i', '', trim($nama));

        return Str::lower(str_replace(' ', '', $nama));
    }

    private function cariKecamatanId(?string $nama): ?string
    {
        if (! $nama) {
            return null;
        }

        return $this->kecamatanMap[$this->normalisasiNamaKecamatan($nama)] ?? null;
    }

    private function prosesSheet($sheet, string $inputBy, $existingNik, bool $dryRun): void
    {
        $highestRow = $sheet->getHighestDataRow();
        $highestCol = $sheet->getHighestDataColumn();

        $headerRow = null;
        $colMap = [];

        for ($row = 1; $row <= min($highestRow, 10); $row++) {
            $values = $sheet->rangeToArray("A{$row}:{$highestCol}{$row}")[0];
            foreach ($values as $colIdx => $val) {
                if (is_string($val) && trim($val) === 'Npsn') {
                    $headerRow = $row;
                    foreach ($values as $i => $label) {
                        if (is_string($label) && trim($label) !== '') {
                            $colMap[trim($label)] = $i;
                        }
                    }
                    break 2;
                }
            }
        }

        if ($headerRow === null) {
            $this->errorRows[] = "[{$sheet->getTitle()}] header 'Npsn' tidak ditemukan, sheet dilewati.";

            return;
        }

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $values = $sheet->rangeToArray("A{$row}:{$highestCol}{$row}")[0];

            $ambil = fn (string $key) => isset($colMap[$key]) ? trim((string) ($values[$colMap[$key]] ?? '')) : null;

            $npsn = $ambil('Npsn');
            $nik = $ambil('Nik');

            if (! $npsn && ! $nik) {
                continue; // baris kosong
            }

            try {
                $sekolahId = $this->pastikanSekolah(
                    npsn: $npsn,
                    namaSekolah: $ambil('Nama Sekolah'),
                    statusSekolah: strtoupper((string) $ambil('Status Sekolah')),
                    bentukPendidikan: $ambil('Bentuk Pendidikan'),
                    namaKecamatanSekolah: $ambil('Sekolah Kecamatan'),
                    dryRun: $dryRun,
                    sheetName: $sheet->getTitle(),
                    rowNumber: $row,
                );

                $kecamatanDomisiliId = $this->cariKecamatanId($ambil('Kecamatan'));

                if (! $kecamatanDomisiliId) {
                    throw new \RuntimeException("Kecamatan domisili '{$ambil('Kecamatan')}' tidak dikenali");
                }

                if (! preg_match('/^\d{16}$/', (string) $nik)) {
                    throw new \RuntimeException("NIK tidak valid: '{$nik}'");
                }

                if ($existingNik->has($nik)) {
                    $this->totalDilewati++;

                    continue;
                }

                $status = strtoupper((string) $ambil('Status'));
                if (! in_array($status, ['LTM', 'DO'], true)) {
                    throw new \RuntimeException("Status tidak valid: '{$status}'");
                }

                $prioritas = $ambil('Prioritas Penjangkauan');
                if (! in_array($prioritas, ['Prioritas 1', 'Prioritas 2', 'Prioritas 3'], true)) {
                    throw new \RuntimeException("Prioritas tidak valid: '{$prioritas}'");
                }

                $tanggalLahir = $this->parseTanggal($ambil('Tanggal Lahir'));

                if (! $dryRun) {
                    DataAts::create([
                        'sekolah_id' => $sekolahId,
                        'kecamatan_domisili_id' => $kecamatanDomisiliId,
                        'nik' => $nik,
                        'nisn' => $ambil('Nisn'),
                        'nama_siswa' => $this->bersihkanTeks($ambil('Nama')),
                        'tempat_lahir' => $this->bersihkanTeks($ambil('Tempat Lahir')),
                        'tanggal_lahir' => $tanggalLahir,
                        'provinsi' => $ambil('Provinsi'),
                        'kabupaten' => $ambil('Kabupaten'),
                        'status' => $status,
                        'alasan_approval' => $ambil('Alasan Approval'),
                        'prioritas_penjangkauan' => $prioritas,
                        'input_by' => $inputBy,
                    ]);
                }

                $existingNik->put($nik, true);
                $this->totalDataBaru++;
            } catch (\Throwable $e) {
                $this->errorRows[] = "[{$sheet->getTitle()} baris {$row}] " . $e->getMessage();
            }
        }
    }

    private function pastikanSekolah(
        ?string $npsn,
        ?string $namaSekolah,
        ?string $statusSekolah,
        ?string $bentukPendidikan,
        ?string $namaKecamatanSekolah,
        bool $dryRun,
        string $sheetName,
        int $rowNumber,
    ): string {
        if (! $npsn) {
            throw new \RuntimeException('NPSN kosong, tidak bisa menentukan sekolah.');
        }

        $sekolah = Sekolah::where('npsn', $npsn)->first();

        if ($sekolah) {
            $kecamatanIdBaru = $this->cariKecamatanId($namaKecamatanSekolah);
            if ($kecamatanIdBaru && $kecamatanIdBaru !== $sekolah->kecamatan_id) {
                $this->konflikKecamatanSekolah[] = [
                    'npsn' => $npsn,
                    'nama' => $namaSekolah,
                    'dipakai' => $this->sekolahKecamatanTerpakai[$npsn] ?? '?',
                    'ditemukan' => $namaKecamatanSekolah,
                    'sheet' => $sheetName,
                    'row' => $rowNumber,
                ];
            }

            return $sekolah->id;
        }

        if (! in_array($statusSekolah, ['NEGERI', 'SWASTA'], true)) {
            throw new \RuntimeException("Status sekolah tidak valid untuk NPSN {$npsn}: '{$statusSekolah}'");
        }

        $kecamatanId = $this->cariKecamatanId($namaKecamatanSekolah);
        $kecamatanLuarNama = null;
        $kabupatenSekolah = 'Kab. Lebak';

        if (! $kecamatanId) {
            // Sekolah di luar Kabupaten Lebak — TETAP DIIMPOR, bukan error.
            // Siswa domisili Lebak, hanya sekolahnya di kabupaten tetangga.
            $kecamatanLuarNama = $namaKecamatanSekolah;
            $key = $this->normalisasiNamaKecamatan((string) $namaKecamatanSekolah);
            $kabupatenSekolah = self::KABUPATEN_TETANGGA[$key] ?? 'Luar Kab. Lebak';

            $this->infoLuarLebak[] = "[{$sheetName} baris {$rowNumber}] Sekolah '{$namaSekolah}' (NPSN {$npsn}) berada di {$kabupatenSekolah}, kecamatan '{$namaKecamatanSekolah}' — tetap diimpor.";
        }

        $this->sekolahKecamatanTerpakai[$npsn] = $namaKecamatanSekolah;

        if ($dryRun) {
            $this->totalSekolahBaru++;

            return (string) Str::uuid();
        }

        $sekolah = Sekolah::create([
            'npsn' => $npsn,
            'nama_sekolah' => $namaSekolah,
            'status_sekolah' => $statusSekolah,
            'bentuk_pendidikan' => $bentukPendidikan,
            'kecamatan_id' => $kecamatanId, // null kalau di luar Lebak
            'kecamatan_luar_nama' => $kecamatanLuarNama,
            'kabupaten_sekolah' => $kabupatenSekolah,
        ]);

        $this->totalSekolahBaru++;

        return $sekolah->id;
    }

    private function parseTanggal(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Rapikan spasi nyasar di awal/akhir dan koma tunggal yang nyangkut di ujung teks,
     * mis. "Rio " -> "Rio", "Lebak," -> "Lebak".
     */
    private function bersihkanTeks(?string $value): ?string
    {
        if (! $value) {
            return $value;
        }

        return trim(rtrim(trim($value), ','));
    }
}