<?php

namespace App\Notifications;

use App\Models\DataAts;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LaporanDiverifikasiNotification extends Notification
{
    use Queueable;

    public function __construct(protected DataAts $data, protected bool $disetujui) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $pesan = $this->disetujui
            ? "Laporan ATS {$this->data->nama_siswa} sudah diverifikasi dan disetujui verifikator."
            : "Laporan ATS {$this->data->nama_siswa} ditolak verifikator. Alasan: {$this->data->catatan_verifikasi}";

        return [
            'data_ats_id' => $this->data->id,
            'nama_siswa' => $this->data->nama_siswa,
            'status_verifikasi' => $this->data->status_verifikasi,
            'pesan' => $pesan,
        ];
    }
}
