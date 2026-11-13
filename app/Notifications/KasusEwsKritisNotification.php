<?php

namespace App\Notifications;

use App\Models\DataAts;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KasusEwsKritisNotification extends Notification
{
    use Queueable;

    public function __construct(protected DataAts $data) {}

    /**
     * Default hanya 'database' (aman, tidak butuh SMTP).
     * Tambahkan 'mail' setelah .env mail server sudah dikonfigurasi.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
        // return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'data_ats_id'   => $this->data->id,
            'nama_siswa'    => $this->data->nama_siswa,
            'sekolah'       => $this->data->sekolah->nama_sekolah,
            'kecamatan'     => $this->data->kecamatanDomisili->nama_kecamatan,
            'ews_score'     => $this->data->ews_score,
            'pesan'         => "Kasus kritis: {$this->data->nama_siswa} ({$this->data->sekolah->nama_sekolah}) naik ke level Merah (skor {$this->data->ews_score}).",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kasus ATS Kritis — ' . $this->data->nama_siswa)
            ->greeting('Peringatan Early Warning System')
            ->line("Siswa **{$this->data->nama_siswa}** dari **{$this->data->sekolah->nama_sekolah}** baru saja naik ke level risiko **Merah** (skor {$this->data->ews_score}).")
            ->line("Kecamatan: {$this->data->kecamatanDomisili->nama_kecamatan}")
            ->line("Status: {$this->data->status} | Prioritas: {$this->data->prioritas_penjangkauan}")
            ->line("Alasan: {$this->data->alasan_approval}")
            ->action('Lihat Detail', url('/pengawas/ews'))
            ->line('Segera lakukan tindak lanjut.');
    }
}