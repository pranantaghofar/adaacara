<?= view('errors/public_not_found', [
    'title' => 'Terjadi kesalahan',
    'headline' => 'Ada yang belum berjalan semestinya',
    'code' => '500',
    'message' => 'Maaf, halaman ini belum bisa ditampilkan sekarang. Silakan coba lagi beberapa saat lagi atau kembali ke halaman utama.',
    'homeUrl' => site_url('/'),
    'primaryLabel' => 'Kembali ke Beranda',
    'primaryUrl' => site_url('/'),
    'secondaryLabel' => 'Lihat Template',
    'secondaryUrl' => site_url('templates'),
    'note' => 'Detail teknis disembunyikan agar website tetap aman dan nyaman untuk pengunjung.',
]) ?>
