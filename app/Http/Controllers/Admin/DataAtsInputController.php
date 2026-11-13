<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use App\Models\Kecamatan;
use App\Models\Sekolah;
use App\Services\EwsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataAtsInputController extends Controller
{
    private const KOLOM = ['npsn', 'nisn', 'nama_siswa', 'kecamatan_domisili', 'status', 'prioritas_penjangkauan', 'alasan_approval', 'tempat_lahir', 'tanggal_lahir'];
    private const WAJIB = ['npsn', 'nisn', 'nama_siswa', 'kecamatan_domisili', 'status', 'prioritas_penjangkauan'];
    private const MAKS_BARIS = 2000;


    public function create(): View
    {
        return view('admin.data-ats.create', [
            'kecamatan' => Kecamatan::orderBy('nama_kecamatan')->get(),
            'sekolah' => Sekolah::orderBy('nama_sekolah')->get(['id', 'nama_sekolah']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sekolah_id' => ['required', 'uuid', 'exists:sekolahs,id'],
            'kecamatan_domisili_id' => ['required', 'uuid', 'exists:kecamatans,id'],
            'nisn' => ['required', 'digits:10', Rule::unique('data_ats', 'nisn')],
            'nama_siswa' => ['required', 'string', 'max:150'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'status' => ['required', 'in:LTM,DO'],
            'alasan_approval' => ['nullable', 'string', 'max:255'],
            'prioritas_penjangkauan' => ['required', 'in:Prioritas 1,Prioritas 2,Prioritas 3'],
        ]);

        $validated['input_by'] = Auth::id();
        $validated['status_verifikasi'] = 'Menunggu Verifikasi';
        DataAts::create($validated);

        return redirect()->route('admin.data-ats.index', ['status_verifikasi' => 'Menunggu Verifikasi'])
            ->with('success', 'Data tersimpan dan masuk antrean verifikasi. Data baru tampil di Data ATS setelah disetujui verifikator.');
    }

    public function importForm(): View
    {
        return view('admin.data-ats.import', ['daftarAlasan' => app(EwsService::class)->daftarAlasan()]);
    }

    public function template(): StreamedResponse
    {
        $book = new Spreadsheet();
        $s = $book->getActiveSheet();
        $s->setTitle('Data');
        $s->fromArray(self::KOLOM, null, 'A1');
        $s->getStyle('A1:I1')->getFont()->setBold(true);
        $s->getStyle('A2:B2001')->getNumberFormat()->setFormatCode('@');
        $s->setCellValueExplicit('A2', '20200001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $s->setCellValueExplicit('B2', '0012345678', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $s->fromArray(['Nama Siswa Contoh', 'Rangkasbitung', 'DO', 'Prioritas 2', 'Bekerja', 'Rangkasbitung', '2008-05-17'], null, 'C2');
        foreach (range('A', 'I') as $c) {
            $s->getColumnDimension($c)->setAutoSize(true);
        }

        $k = $book->createSheet();
        $k->setTitle('Daftar Kecamatan');
        $k->setCellValue('A1', 'Nama kecamatan yang diterima (kolom kecamatan_domisili)');
        $k->getStyle('A1')->getFont()->setBold(true);
        foreach (Kecamatan::orderBy('nama_kecamatan')->pluck('nama_kecamatan') as $i => $n) {
            $k->setCellValue('A' . ($i + 2), $n);
        }
        $k->getColumnDimension('A')->setAutoSize(true);
        $sk = $book->createSheet();
        $sk->setTitle('Daftar Sekolah');
        $sk->fromArray(['npsn', 'nama_sekolah'], null, 'A1');
        $sk->getStyle('A1:B1')->getFont()->setBold(true);
        foreach (Sekolah::orderBy('nama_sekolah')->get(['npsn', 'nama_sekolah']) as $i => $r) {
            $sk->setCellValueExplicit('A' . ($i + 2), (string) $r->npsn, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sk->setCellValue('B' . ($i + 2), $r->nama_sekolah);
        }
        $sk->getColumnDimension('A')->setAutoSize(true);
        $sk->getColumnDimension('B')->setAutoSize(true);
        $book->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($book) {
            (new Xlsx($book))->save('php://output');
        }, 'template-import-data-ats.xlsx');
    }

    public function importStore(Request $request, EwsService $ews): RedirectResponse
    {
        $request->validate(
            ['berkas' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:5120']],
            ['berkas.required' => 'Pilih file terlebih dahulu.', 'berkas.mimes' => 'Format file harus xlsx, xls, atau csv.', 'berkas.max' => 'Ukuran file maksimal 5 MB.']
        );

        try {
            $file = $request->file('berkas');
            $ext = strtolower($file->getClientOriginalExtension());
            $reader = IOFactory::createReader(in_array($ext, ['csv', 'txt']) ? 'Csv' : ($ext === 'xls' ? 'Xls' : 'Xlsx'));
            if ($reader instanceof Csv) {
                $h = fopen($file->getRealPath(), 'r');
                $baris1 = (string) fgets($h);
                fclose($h);
                $reader->setDelimiter(substr_count($baris1, ';') > substr_count($baris1, ',') ? ';' : ',');
            }
            $raw = $reader->load($file->getRealPath())->getSheet(0)->toArray(null, false, false, false);
        } catch (\Throwable $e) {
            return back()->withErrors(['berkas' => 'File tidak bisa dibaca. Pastikan memakai template yang disediakan.']);
        }

        $header = array_map(
            fn ($h) => Str::of(ltrim((string) $h, "\xEF\xBB\xBF"))->trim()->lower()->replace([' ', '-'], '_')->toString(),
            array_shift($raw) ?? []
        );
        $hilang = array_diff(self::WAJIB, $header);
        if ($hilang) {
            return back()->withErrors(['berkas' => 'Kolom wajib tidak ditemukan: ' . implode(', ', $hilang) . '. Gunakan template.']);
        }
        if (count($raw) > self::MAKS_BARIS) {
            return back()->withErrors(['berkas' => 'Maksimal ' . self::MAKS_BARIS . ' baris per file. Pecah file Anda.']);
        }

        $kecMap = [];
        foreach (Kecamatan::all() as $k) {
            $kecMap[$this->norm($k->nama_kecamatan)] = $k->id;
        }
        $sekolahMap = Sekolah::pluck('id', 'npsn')->mapWithKeys(fn ($id, $npsn) => [trim((string) $npsn) => $id])->all();
        $nisnAda = [];
        foreach (DataAts::pluck('nisn') as $n) {
            $nisnAda[ltrim((string) $n, '0')] = true;
        }

        $baris = [];
        $galat = [];
        $now = now();

        foreach ($raw as $i => $cells) {
            $no = $i + 2;
            if (! array_filter($cells, fn ($c) => $c !== null && trim((string) $c) !== '')) {
                continue;
            }
            $r = [];
            foreach ($header as $idx => $h) {
                $v = $cells[$idx] ?? null;
                $r[$h] = is_string($v) ? trim($v) : $v;
            }
            $tolak = function (string $alasan) use (&$galat, $no, $r) {
                $galat[] = ['baris' => $no, 'nisn' => (string) ($r['nisn'] ?? ''), 'alasan' => $alasan];
            };

            foreach (self::WAJIB as $w) {
                if (($r[$w] ?? '') === '' || $r[$w] === null) {
                    $tolak("Kolom {$w} kosong");
                    continue 2;
                }
            }

            $sekolahId = $sekolahMap[trim((string) $r['npsn'])] ?? null;
            if (! $sekolahId) {
                $tolak("NPSN '{$r['npsn']}' tidak ditemukan (lihat sheet Daftar Sekolah)");
                continue;
            }

            $digit = preg_replace('/\D/', '', (string) $r['nisn']);
            if (strlen($digit) < 8 || strlen($digit) > 10) {
                $tolak('NISN harus 10 digit');
                continue;
            }
            $nisn = str_pad($digit, 10, '0', STR_PAD_LEFT);
            if (isset($nisnAda[ltrim($nisn, '0')])) {
                $tolak('NISN sudah terdaftar / dobel di file');
                continue;
            }

            $status = strtoupper((string) $r['status']);
            if (! in_array($status, ['LTM', 'DO'], true)) {
                $tolak('Status harus LTM atau DO');
                continue;
            }
            if (! preg_match('/^(?:prioritas\s*)?([123])$/i', trim((string) $r['prioritas_penjangkauan']), $m)) {
                $tolak('Prioritas harus 1, 2, atau 3');
                continue;
            }
            $kec = $kecMap[$this->norm((string) $r['kecamatan_domisili'])] ?? null;
            if (! $kec) {
                $tolak("Kecamatan '{$r['kecamatan_domisili']}' tidak dikenali (lihat sheet Daftar Kecamatan)");
                continue;
            }
            $tgl = null;
            if (($r['tanggal_lahir'] ?? '') !== '' && $r['tanggal_lahir'] !== null) {
                $tgl = $this->tanggal($r['tanggal_lahir']);
                if (! $tgl) {
                    $tolak('Format tanggal_lahir tidak dikenali (pakai yyyy-mm-dd)');
                    continue;
                }
            }

            $nisnAda[ltrim($nisn, '0')] = true;
            $b = [
                'sekolah_id' => $sekolahId,
                'kecamatan_domisili_id' => $kec,
                'nisn' => $nisn,
                'nama_siswa' => mb_substr((string) $r['nama_siswa'], 0, 150),
                'tempat_lahir' => ($r['tempat_lahir'] ?? '') !== '' ? mb_substr((string) $r['tempat_lahir'], 0, 100) : null,
                'tanggal_lahir' => $tgl,
                'status' => $status,
                'alasan_approval' => ($r['alasan_approval'] ?? '') !== '' ? mb_substr((string) $r['alasan_approval'], 0, 255) : null,
                'prioritas_penjangkauan' => 'Prioritas ' . $m[1],
                'status_verifikasi' => 'Menunggu Verifikasi',
                'sumber_data' => 'IMPORT-ADMIN',
                'input_by' => Auth::id(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $skor = $ews->hitungSkor((new DataAts)->forceFill($b));
            $b['ews_score'] = $skor;
            $b['ews_level'] = $ews->tentukanLevel($skor);
            $baris[] = $b;
        }

        if ($baris) {
            DB::transaction(function () use ($baris) {
                foreach (array_chunk($baris, 200) as $chunk) {
                    DB::table('data_ats')->insert($chunk);
                }
            });
        }

        $pesan = count($baris) . ' data berhasil diimpor dan masuk antrean verifikasi (belum tampil di Data ATS sebelum disetujui verifikator).'
            . ($galat ? ' ' . count($galat) . ' baris dilewati.' : '');
        $tujuan = $galat ? 'admin.data-ats.import' : 'admin.data-ats.index';

        return redirect()->route($tujuan)->with('success', $pesan)->with('import_galat', $galat);
    }

    private function tanggal(mixed $v): ?string
    {
        try {
            if (is_numeric($v)) {
                return XlDate::excelToDateTimeObject((float) $v)->format('Y-m-d');
            }
            $v = trim((string) $v);
            if (preg_match('#^(\d{1,2})[/-](\d{1,2})[/-](\d{4})$#', $v, $m)) {
                return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            }

            return \Carbon\Carbon::parse($v)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function norm(string $nama): string
    {
        return Str::lower(str_replace(' ', '', preg_replace('/^kec\.?\s*/i', '', trim($nama))));
    }
}