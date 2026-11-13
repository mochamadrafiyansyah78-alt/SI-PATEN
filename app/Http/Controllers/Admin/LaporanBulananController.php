<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataAts;
use App\Models\Kecamatan;
use App\Services\MonitoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class LaporanBulananController extends Controller
{
    public function __construct(protected MonitoringService $monitoring) {}

    public function index(Request $request): View
    {
        $data = $this->siapkanData($request);

        return view('admin.laporan.index', $data);
    }

    public function exportPdf(Request $request): Response
    {
        $data = $this->siapkanData($request);

        $pdf = Pdf::loadView('admin.laporan.pdf', $data)->setPaper('a4', 'landscape');

        return $pdf->download("laporan-ats-{$data['bulan']}-{$data['tahun']}.pdf");
    }

    private function siapkanData(Request $request): array
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        $rekapKecamatan = Kecamatan::withCount([
            'dataAts as total' => fn ($q) => $q->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun),
            'dataAts as jumlah_ltm' => fn ($q) => $q->whereMonth('created_at', $bulan)
                ->whereYear('created_at', $tahun)->where('status', 'LTM'),
            'dataAts as jumlah_do' => fn ($q) => $q->whereMonth('created_at', $bulan)
                ->whereYear('created_at', $tahun)->where('status', 'DO'),
        ])->orderByDesc('total')->get();

        $rekapStatusPenanganan = DataAts::disetujui()
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->selectRaw('status_penanganan, count(*) as jumlah')
            ->groupBy('status_penanganan')
            ->get();

        $rekapAlasan = DataAts::disetujui()
            ->whereMonth('created_at', $bulan)
            ->whereYear('created_at', $tahun)
            ->selectRaw('alasan_approval, count(*) as jumlah')
            ->groupBy('alasan_approval')
            ->orderByDesc('jumlah')
            ->get();

        $rankingSekolah = $this->monitoring->rankingSekolah($bulan, $tahun);
        $kasusMengendap = $this->monitoring->kasusMengendap();

        return compact('bulan', 'tahun', 'rekapKecamatan', 'rekapStatusPenanganan', 'rekapAlasan', 'rankingSekolah', 'kasusMengendap');
    }
}