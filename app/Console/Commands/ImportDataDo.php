<?php

namespace App\Console\Commands;

use App\Models\DataAts;
use App\Models\Kecamatan;
use App\Models\Sekolah;
use App\Models\User;
use App\Services\EwsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportDataDo extends Command
{
    protected $signature = 'import:data-do
        {path : Path ke Data_DO_Lebak_BERSIH.xlsx (sheet "Data Bersih")}
        {--input-by= : UUID user pencatat, default admin_dinas pertama}
        {--tanggal= : Tanggal pencatatan (Y-m-d) untuk created_at, default hari ini}
        {--dry-run : Simulasi saja, tidak menyimpan apa pun}';

    protected $description = 'Import data siswa DO SMA/SMK (file bersih) ke tabel data_ats';

    private const WAJIB = ['nisn', 'nama_siswa', 'kecamatan_domisili', 'npsn', 'nama_sekolah', 'status', 'prioritas_penjangkauan'];

    public function handle(EwsService $ews): int
    {
        $path = $this->argument('path');
        if (! file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $inputBy = $this->option('input-by') ?: User::where('role', 'admin_dinas')->value('id');
        if (! $inputBy) {
            $this->error('Tidak ada admin_dinas untuk dijadikan pencatat. Gunakan --input-by=<uuid>.');

            return self::FAILURE;
        }

        $tanggal = $this->option('tanggal') ? \Carbon\Carbon::parse($this->option('tanggal'))->startOfDay() : now();

        $book = IOFactory::load($path);
        $sheet = $book->getSheetByName('Data Bersih') ?? $book->getSheet(0);
        $raw = $sheet->toArray(null, false, false, false);
        $header = array_map(fn ($h) => trim((string) $h), array_shift($raw));

        $kecMap = [];
        foreach (Kecamatan::all() as $k) {
            $kecMap[$this->norm($k->nama_kecamatan)] = $k->id;
        }

        $nisnAda = [];
        foreach (DataAts::pluck('nisn') as $n) {
            $nisnAda[ltrim((string) $n, '0')] = true;
        }
        $sekolahMap = Sekolah::pluck('id', 'npsn')->all();

        $baris = [];
        $sekolahBaru = [];
        $dilewati = 0;
        $galat = [];
        $statusCount = [];

        foreach ($raw as $i => $cells) {
            $no = $i + 2;
            if (! array_filter($cells, fn ($c) => $c !== null && $c !== '')) {
                continue;
            }
            $r = [];
            foreach ($header as $idx => $h) {
                $v = $cells[$idx] ?? null;
                $r[$h] = is_string($v) ? trim($v) : $v;
            }

            foreach (self::WAJIB as $w) {
                if (($r[$w] ?? '') === '' || $r[$w] === null) {
                    $galat[] = "Baris {$no}: kolom {$w} kosong";
                    continue 2;
                }
            }

            $nisn = str_pad(preg_replace('/\D/', '', (string) $r['nisn']), 10, '0', STR_PAD_LEFT);
            if (! preg_match('/^\d{10}$/', $nisn)) {
                $galat[] = "Baris {$no}: NISN tidak valid ({$r['nisn']})";
                continue;
            }
            if (isset($nisnAda[ltrim($nisn, '0')])) {
                $dilewati++;
                continue;
            }
            if (! in_array($r['status'], ['LTM', 'DO'], true)) {
                $galat[] = "Baris {$no}: status tidak valid ({$r['status']})";
                continue;
            }
            if (! in_array($r['prioritas_penjangkauan'], ['Prioritas 1', 'Prioritas 2', 'Prioritas 3'], true)) {
                $galat[] = "Baris {$no}: prioritas tidak valid ({$r['prioritas_penjangkauan']})";
                continue;
            }
            $kecDomisili = $kecMap[$this->norm((string) $r['kecamatan_domisili'])] ?? null;
            if (! $kecDomisili) {
                $galat[] = "Baris {$no}: kecamatan domisili '{$r['kecamatan_domisili']}' tidak dikenali";
                continue;
            }

            $npsn = (string) $r['npsn'];
            if (! isset($sekolahMap[$npsn])) {
                $kecSekolah = $kecMap[$this->norm((string) ($r['kecamatan_sekolah'] ?? ''))] ?? null;
                $sekolahBaru[$npsn] = [
                    'npsn' => $npsn,
                    'nama_sekolah' => $r['nama_sekolah'],
                    'status_sekolah' => $r['status_sekolah'] ?: 'NEGERI',
                    'bentuk_pendidikan' => $r['bentuk_pendidikan'] ?: 'SMA',
                    'kecamatan_id' => $kecSekolah,
                    'kecamatan_luar_nama' => $kecSekolah ? null : ($r['kecamatan_sekolah'] ?? null),
                    'kabupaten_sekolah' => 'Kab. Lebak',
                ];
                $sekolahMap[$npsn] = null; // ditandai agar tidak dibuat dua kali
            }

            $nisnAda[ltrim($nisn, '0')] = true;
            $verif = $r['status_verifikasi'] ?: 'Menunggu Verifikasi';
            $statusCount[$verif] = ($statusCount[$verif] ?? 0) + 1;

            $baris[] = [
                '_npsn' => $npsn,
                'kecamatan_domisili_id' => $kecDomisili,
                'nik' => null,
                'nisn' => $nisn,
                'nama_siswa' => $r['nama_siswa'],
                'jenis_kelamin' => $r['jenis_kelamin'] ?: null,
                'usia' => is_numeric($r['usia'] ?? null) ? (int) $r['usia'] : null,
                'desa_kelurahan' => $r['desa_kelurahan'] ?: null,
                'tingkat_pendidikan' => $r['tingkat_pendidikan'] ?: null,
                'status' => $r['status'],
                'alasan_approval' => $r['alasan_approval'] ?: null,
                'keterangan_alasan' => $r['keterangan_alasan'] ?: null,
                'prioritas_penjangkauan' => $r['prioritas_penjangkauan'],
                'prioritas_diturunkan' => (bool) ($r['prioritas_diturunkan'] ?? 1),
                'status_verifikasi' => $verif,
                'catatan_verifikasi' => $r['catatan_verifikasi'] ?: null,
                'sumber_data' => $r['sumber_data'] ?: null,
                'input_by' => $inputBy,
                'created_at' => $tanggal,
                'updated_at' => $tanggal,
            ];
        }

        $this->info('Baris siap impor     : ' . count($baris));
        $this->info('Sekolah baru          : ' . count($sekolahBaru));
        $this->info('Dilewati (NISN ada)   : ' . $dilewati);
        foreach ($statusCount as $s => $n) {
            $this->line("  - {$s}: {$n}");
        }
        if ($galat) {
            $this->warn(count($galat) . ' baris dilewati karena galat:');
            foreach (array_slice($galat, 0, 30) as $g) {
                $this->line('  - ' . $g);
            }
        }

        if ($dryRun) {
            $this->warn('Mode --dry-run: tidak ada data yang disimpan.');

            return self::SUCCESS;
        }
        if (! $baris) {
            $this->warn('Tidak ada data baru untuk diimpor.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($baris, $sekolahBaru, &$sekolahMap, $ews) {
            foreach ($sekolahBaru as $npsn => $attr) {
                $sekolahMap[$npsn] = Sekolah::create($attr)->id;
            }

            $rows = [];
            foreach ($baris as $b) {
                $b['sekolah_id'] = $sekolahMap[$b['_npsn']];
                unset($b['_npsn']);

                $model = (new DataAts)->forceFill($b);
                $b['ews_score'] = $ews->hitungSkor($model);
                $b['ews_level'] = $ews->tentukanLevel($b['ews_score']);
                $rows[] = $b;
            }

            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table('data_ats')->insert($chunk);
            }
        });

        $this->info('Selesai. Data masuk ke admin (semua) dan pengawas (sesuai sekolahnya).');

        return self::SUCCESS;
    }

    private function norm(string $nama): string
    {
        return Str::lower(str_replace(' ', '', preg_replace('/^kec\.?\s*/i', '', trim($nama))));
    }
}
