<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; }
        th { background: #eee; }
        h1 { font-size: 16px; } h2 { font-size: 13px; margin-top: 20px; }
    </style>
</head>
<body>
    <h1>Laporan ATS Kabupaten Lebak — {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}</h1>

    <h2>Rekap per Kecamatan</h2>
    <table>
        <thead><tr><th>Kecamatan</th><th>LTM</th><th>DO</th><th>Total</th></tr></thead>
        <tbody>
            @foreach ($rekapKecamatan as $k)
                <tr><td>{{ $k->nama_kecamatan }}</td><td>{{ $k->jumlah_ltm }}</td><td>{{ $k->jumlah_do }}</td><td>{{ $k->total }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>Ranking Sekolah — Monitoring Intervensi</h2>
    <table>
        <thead><tr><th>Sekolah</th><th>Total</th><th>Selesai</th><th>Mengendap</th><th>Rasio Selesai</th></tr></thead>
        <tbody>
            @foreach ($rankingSekolah as $s)
                <tr><td>{{ $s->nama_sekolah }}</td><td>{{ $s->total }}</td><td>{{ $s->selesai }}</td><td>{{ $s->mengendap }}</td><td>{{ $s->rasio_selesai }}%</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>