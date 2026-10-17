<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountVerificationNotification extends Notification
{
    use Queueable;

    public function __construct(public string $verificationUrl)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Aktivasi dan Verifikasi Akun SIAP TUNTAS')
            ->greeting('Yth. ' . $notifiable->name . ',')
            ->line('Akun Anda di SIAP TUNTAS (Sinergi Aksi Percepatan Tindak Lanjut Hasil Pemeriksaan Untuk Perbaikan Tata Kelola Pemerintahan Kabupaten Rembang')
            ->line('Untuk dapat mulai menggunakan akun Anda, masuk melalui Google, serta mengaktifkan fitur keamanan seperti reset kata sandi, silakan lakukan verifikasi dan aktivasi akun Anda.')
            ->action('Verifikasi & Aktifkan Akun', $this->verificationUrl)
            ->line('Tautan verifikasi ini berlaku selama 60 menit.')
            ->line('Setelah akun diverifikasi, Anda dapat membuat kata sandi baru atau masuk secara instan menggunakan akun Google resmi Anda.')
            ->line('Jika Anda merasa tidak memiliki kaitan dengan Inspektorat Kabupaten Rembang, Anda dapat mengabaikan email ini.')
            ->salutation("Salam hangat,\nInspektorat Kabupaten Rembang");
    }
}
