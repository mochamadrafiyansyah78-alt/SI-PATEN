<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $judul }} · SI-PATEN</title>
    <style>
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f1f5f9;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;color:#1e293b}
        .k{max-width:420px;margin:16px;background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,57,95,.12);overflow:hidden;text-align:center}
        .r{height:6px;background:linear-gradient(90deg,#00395f 33%,#77612A 33% 66%,#62B38B 66%)}
        .b{padding:32px 28px}.c{font-size:56px;font-weight:800;color:#00395f;line-height:1}
        h1{font-size:20px;margin:12px 0 6px}p{color:#64748b;font-size:14px;line-height:1.6;margin:0 0 22px}
        a,button{display:inline-block;border:0;cursor:pointer;background:#00395f;color:#fff;border-radius:10px;padding:10px 18px;font-size:14px;font-weight:700;text-decoration:none;margin:3px}
        a.s{background:#e2e8f0;color:#334155}
    </style>
</head>
<body>
<div class="k"><div class="r"></div><div class="b">
    <div class="c">{{ $kode }}</div>
    <h1>{{ $judul }}</h1>
    <p>{{ $pesan }}</p>
    <button type="button" onclick="location.reload()">Muat Ulang</button>
    <a class="s" href="{{ url('/') }}">Ke Beranda</a>
</div></div>
</body>
</html>
