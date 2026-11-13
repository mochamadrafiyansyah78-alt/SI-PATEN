<?php

namespace App\Observers;

use App\Models\DataAts;
use App\Models\RiwayatPenanganan;
use App\Models\User;
use App\Notifications\KasusEwsKritisNotification;
use App\Services\EwsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DataAtsObserver
{
    /** Penampung sementara path lampiran per data_ats_id: diisi controller, dibaca lalu dibuang di updated(). */
    public static array $pendingLampiran = [];

    public function __construct(protected EwsService $ews) {}

    public function created(DataAts $data): void
    {
        $this->hitungDanNotifikasi($data, previousLevel: 'Hijau'); // default sebelum dihitung

        RiwayatPenanganan::create([
            'data_ats_id' => $data->id,
            'status_penanganan' => $data->status_penanganan ?? 'Baru',
            'catatan' => 'Data ATS pertama kali dicatat.',
            'diubah_oleh' => $data->input_by,
            'created_at' => $data->created_at,
        ]);
    }

    public function updated(DataAts $data): void
    {
        if ($data->wasChanged(['status', 'prioritas_penjangkauan', 'alasan_approval', 'status_penanganan'])) {
            $this->hitungDanNotifikasi($data, previousLevel: $data->ews_level);
        }

        $adaLampiran = isset(self::$pendingLampiran[$data->id]);
        $catatanBaru = $data->wasChanged('catatan_penanganan') && filled($data->catatan_penanganan);

        if ($data->wasChanged('status_penanganan') || $catatanBaru || $adaLampiran) {
            RiwayatPenanganan::create([
                'data_ats_id' => $data->id,
                'status_penanganan' => $data->status_penanganan,
                'catatan' => $data->catatan_penanganan,
                'lampiran_path' => self::$pendingLampiran[$data->id] ?? null,
                'diubah_oleh' => $data->ditangani_oleh ?? Auth::id(),
                'created_at' => $data->status_updated_at ?? now(),
            ]);

            unset(self::$pendingLampiran[$data->id]);
        }
    }

    protected function hitungDanNotifikasi(DataAts $data, string $previousLevel): void
    {
        $this->ews->refresh($data);

        // Hanya kirim notifikasi saat TRANSISI masuk ke Merah (bukan tiap kali dihitung ulang)
        if ($data->ews_level === 'Merah' && $previousLevel !== 'Merah') {
            $this->kirimNotifikasi($data);
        }
    }

    protected function kirimNotifikasi(DataAts $data): void
    {
        try {
            $penerima = User::where('role', 'admin_dinas')
                ->orWhere(function ($q) use ($data) {
                    $q->where('role', 'pengawas_sekolah')
                      ->where('sekolah_id', $data->sekolah_id);
                })
                ->get();

            if ($penerima->isNotEmpty()) {
                Notification::send($penerima, new KasusEwsKritisNotification($data));
            }
        } catch (\Throwable $e) {
            // Jangan sampai gagal kirim notifikasi menggagalkan proses simpan data ATS
            Log::warning('Gagal mengirim notifikasi EWS Merah: ' . $e->getMessage());
        }
    }
}
