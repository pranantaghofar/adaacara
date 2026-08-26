<?= view('errors/public_not_found', [
    'title' => 'Halaman tidak ditemukan',
    'headline' => 'Halaman tidak ditemukan',
    'code' => '404',
    'message' => ENVIRONMENT !== 'production' && ! empty($message)
        ? (string) $message
        : 'Link yang kamu buka belum aktif, sudah dihapus, atau alamatnya kurang tepat.',
    'plansUrl' => site_url('plans'),
    'templatesUrl' => site_url('templates'),
    'homeUrl' => site_url('/'),
    'primaryLabel' => 'Lihat Paket Membership',
    'primaryUrl' => site_url('plans'),
    'secondaryLabel' => 'Buat Undangan',
    'secondaryUrl' => site_url('templates'),
    'note' => 'Sudah punya link undangan? Pastikan undangan sudah dipublish dan slug URL ditulis dengan benar.',
]) ?>
