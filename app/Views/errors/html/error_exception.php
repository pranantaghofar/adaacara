<?php
    $exceptionTitle = isset($title) && $title !== '' ? (string) $title : 'Terjadi kesalahan';
    $exceptionMessage = 'Maaf, halaman ini sedang mengalami gangguan. Tim AdaAcara sudah menyiapkan tampilan aman agar detail teknis tidak tampil ke publik.';

    if (ENVIRONMENT !== 'production' && isset($exception) && $exception instanceof Throwable) {
        $exceptionMessage = trim($exception->getMessage()) !== ''
            ? $exception->getMessage()
            : $exceptionMessage;
    }
?>
<?= view('errors/public_not_found', [
    'title' => $exceptionTitle,
    'headline' => 'Terjadi kesalahan',
    'code' => '500',
    'message' => $exceptionMessage,
    'homeUrl' => site_url('/'),
    'primaryLabel' => 'Kembali ke Beranda',
    'primaryUrl' => site_url('/'),
    'secondaryLabel' => 'Lihat Template',
    'secondaryUrl' => site_url('templates'),
    'note' => 'Coba muat ulang halaman beberapa saat lagi. Jika masih terjadi, silakan kembali ke halaman utama.',
]) ?>
