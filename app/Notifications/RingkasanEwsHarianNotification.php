<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class RingkasanEwsHarianNotification extends Notification
{
    use Queueable;

    public function __construct(protected Collection $kasusMerah) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'jumlah'  => $this->kasusMerah->count(),
            'pesan'   => "Ringkasan harian: {$this->kasusMerah->count()} kasus ATS masih berlevel Merah dan belum selesai ditangani.",
            'daftar'  => $this->kasusMerah->take(10)->map(fn ($d) => [
                'nama_siswa' => $d->nama_siswa,
                'sekolah'    => $d->sekolah->nama_sekolah,
                'skor'       => $d->ews_score,
            ])->values(),
        ];
    }
}