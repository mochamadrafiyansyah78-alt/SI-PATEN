<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

// Satu notifikasi untuk satu batch aksi massal verifikator (bukan satu per siswa).
class RingkasanVerifikasiNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected int $jumlah,
        protected bool $disetujui,
        protected ?string $catatan = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $pesan = $this->disetujui
            ? "{$this->jumlah} laporan ATS yang Anda input disetujui verifikator dan menunggu validasi lapangan pengawas."
            : "{$this->jumlah} laporan ATS yang Anda input ditolak verifikator. Alasan: {$this->catatan}";

        return [
            'jumlah' => $this->jumlah,
            'status_verifikasi' => $this->disetujui ? 'Disetujui' : 'Ditolak',
            'pesan' => $pesan,
        ];
    }
}
