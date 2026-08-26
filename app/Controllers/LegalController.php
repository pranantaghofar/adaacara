<?php

namespace App\Controllers;

class LegalController extends BaseController
{
    public function terms(): string
    {
        return view('legal/page', [
            'title' => 'Syarat & Ketentuan - AdaAcara',
            'eyebrow' => 'Legal',
            'heading' => 'Syarat & Ketentuan',
            'description' => 'Ketentuan penggunaan layanan AdaAcara untuk membuat undangan digital, memakai template, menjadi kreator, dan menggunakan fitur penjual.',
            'updatedAt' => '14 Juni 2026',
            'sections' => [
                [
                    'title' => '1. Penerimaan Ketentuan',
                    'body' => [
                        'Dengan mengakses atau menggunakan AdaAcara, kamu menyetujui Syarat & Ketentuan ini. Jika tidak setuju, kamu dapat berhenti menggunakan layanan.',
                        'AdaAcara dapat memperbarui ketentuan ini dari waktu ke waktu. Perubahan penting akan ditampilkan melalui halaman ini atau kanal komunikasi resmi AdaAcara.',
                    ],
                ],
                [
                    'title' => '2. Akun dan Keamanan',
                    'body' => [
                        'Pengguna wajib memberikan data akun yang benar, termasuk nama dan email aktif. Untuk akun baru, login dapat dibatasi sampai email berhasil diverifikasi.',
                        'Pengguna bertanggung jawab menjaga keamanan password, akses email, dan akun Google yang terhubung. Aktivitas yang terjadi melalui akun pengguna dianggap dilakukan oleh pemilik akun tersebut.',
                    ],
                ],
                [
                    'title' => '3. Editor, Template, dan Undangan Digital',
                    'body' => [
                        'Pengguna dapat membuat undangan digital dari template, mengedit desain, mengunggah media, dan mempublikasikan halaman acara sesuai paket atau akses yang dimiliki.',
                        'Pengguna bertanggung jawab atas teks, foto, ilustrasi, musik, data tamu, dan konten lain yang dimasukkan ke dalam undangan.',
                    ],
                ],
                [
                    'title' => '4. Template Kreator dan Marketplace',
                    'body' => [
                        'Kreator dapat mengajukan desain template untuk direview. Template hanya dapat tampil publik setelah melalui persetujuan admin.',
                        'Kreator menyatakan bahwa desain yang diajukan adalah karya sendiri atau memiliki hak yang cukup untuk digunakan dan dipasarkan di AdaAcara.',
                        'AdaAcara berhak menolak, menyembunyikan, atau meminta revisi template yang melanggar hak cipta, mengandung konten terlarang, atau tidak sesuai standar kualitas platform.',
                    ],
                ],
                [
                    'title' => '5. Komisi Kreator',
                    'body' => [
                        'Jika program komisi aktif, kreator yang memenuhi syarat dapat menerima bagian pendapatan dari penggunaan template sesuai skema yang ditampilkan di platform, termasuk skema 70/30 jika berlaku.',
                        'Komisi hanya diproses untuk kreator aktif dan template yang memenuhi ketentuan platform. AdaAcara dapat menunda atau membatalkan komisi jika ditemukan penyalahgunaan, refund, transaksi tidak valid, atau pelanggaran ketentuan.',
                    ],
                ],
                [
                    'title' => '6. Pembayaran, Paket, dan Akses Fitur',
                    'body' => [
                        'Fitur tertentu dapat membutuhkan paket berbayar, verifikasi pembayaran, atau persetujuan admin. Detail harga, durasi aktif, dan batas fitur mengikuti informasi yang tampil saat pembelian.',
                        'Akses fitur dapat berakhir ketika masa aktif paket habis, pembayaran ditolak, atau akun melanggar ketentuan.',
                    ],
                ],
                [
                    'title' => '7. Konten yang Dilarang',
                    'body' => [
                        'Pengguna dilarang mengunggah atau mempublikasikan konten yang melanggar hukum, menipu, mengandung ujaran kebencian, pornografi, kekerasan eksplisit, pelanggaran privasi, malware, atau pelanggaran hak cipta.',
                        'AdaAcara dapat menghapus konten, menonaktifkan undangan, atau membatasi akun jika ditemukan pelanggaran.',
                    ],
                ],
                [
                    'title' => '8. Batas Tanggung Jawab',
                    'body' => [
                        'AdaAcara berupaya menjaga layanan tetap stabil, tetapi tidak menjamin layanan selalu bebas gangguan, bebas kesalahan, atau sesuai untuk semua kebutuhan khusus.',
                        'Pengguna bertanggung jawab melakukan pengecekan isi undangan sebelum dibagikan kepada tamu atau customer.',
                    ],
                ],
                [
                    'title' => '9. Kontak',
                    'body' => [
                        'Pertanyaan terkait ketentuan ini dapat dikirim ke halo@adaacara.com.',
                    ],
                ],
            ],
        ]);
    }

    public function privacy(): string
    {
        return view('legal/page', [
            'title' => 'Kebijakan Privasi - AdaAcara',
            'eyebrow' => 'Privasi',
            'heading' => 'Kebijakan Privasi',
            'description' => 'Penjelasan tentang data yang kami kumpulkan, gunakan, simpan, dan lindungi saat kamu menggunakan AdaAcara.',
            'updatedAt' => '14 Juni 2026',
            'sections' => [
                [
                    'title' => '1. Data yang Kami Kumpulkan',
                    'body' => [
                        'Kami dapat mengumpulkan data akun seperti nama, email, password terenkripsi, status verifikasi email, dan informasi login Google jika kamu menggunakan Google sign-in.',
                        'Kami juga memproses data undangan seperti judul acara, slug, desain, teks, gambar, RSVP, guestbook, ucapan tamu, dan pengaturan publikasi.',
                    ],
                ],
                [
                    'title' => '2. Data Pembayaran, Kreator, dan Penjual',
                    'body' => [
                        'Untuk pembelian paket, kami menyimpan data order seperti paket, nominal, metode pembayaran, status pembayaran, bukti pembayaran, dan waktu transaksi.',
                        'Untuk kreator atau penjual, kami dapat menyimpan nama brand, bio, portofolio, status aplikasi, template yang diajukan, data komisi, dan permintaan pencairan.',
                    ],
                ],
                [
                    'title' => '3. Cara Kami Menggunakan Data',
                    'body' => [
                        'Data digunakan untuk membuat akun, mengamankan login, mengirim email verifikasi/reset password, menyediakan editor, memproses publish undangan, menampilkan template, memproses order, dan mendukung layanan customer.',
                        'Data juga dapat digunakan untuk menjaga keamanan platform, mencegah penyalahgunaan, memperbaiki fitur, dan memenuhi kewajiban hukum yang berlaku.',
                    ],
                ],
                [
                    'title' => '4. Email Transaksional dan Pihak Ketiga',
                    'body' => [
                        'AdaAcara menggunakan penyedia email seperti Brevo untuk mengirim email transaksional, termasuk verifikasi email dan reset password.',
                        'Jika kamu memakai Google login, Google memberikan data dasar seperti email, nama, status verifikasi email, dan foto profil sesuai izin yang kamu setujui.',
                        'Untuk pembayaran atau layanan pendukung lain, data dapat diproses oleh penyedia terkait sesuai kebutuhan transaksi dan keamanan.',
                    ],
                ],
                [
                    'title' => '5. Publikasi Undangan dan Data Tamu',
                    'body' => [
                        'Undangan yang dipublish dapat diakses melalui URL publik. Data yang kamu tampilkan pada undangan, termasuk nama acara, foto, lokasi, dan cerita, dapat dilihat oleh orang yang memiliki link.',
                        'Guestbook dan RSVP diproses untuk kebutuhan pemilik undangan. Pemilik undangan bertanggung jawab memastikan penggunaan data tamu sesuai izin dan konteks acara.',
                    ],
                ],
                [
                    'title' => '6. Penyimpanan dan Keamanan',
                    'body' => [
                        'Kami menerapkan langkah teknis yang wajar untuk melindungi data, termasuk password hash dan token verifikasi/reset yang disimpan dalam bentuk hash.',
                        'Tidak ada sistem yang sepenuhnya bebas risiko. Jika terjadi insiden yang berdampak pada data pengguna, kami akan mengambil langkah penanganan sesuai kemampuan dan ketentuan yang berlaku.',
                    ],
                ],
                [
                    'title' => '7. Hak Pengguna',
                    'body' => [
                        'Pengguna dapat meminta koreksi data akun, penghapusan data tertentu, atau informasi terkait pemrosesan data dengan menghubungi AdaAcara.',
                        'Beberapa data mungkin tetap disimpan jika diperlukan untuk keamanan, pembukuan, penyelesaian sengketa, atau kewajiban hukum.',
                    ],
                ],
                [
                    'title' => '8. Kontak Privasi',
                    'body' => [
                        'Pertanyaan privasi dapat dikirim ke halo@adaacara.com.',
                    ],
                ],
            ],
        ]);
    }

    public function cookies(): string
    {
        return view('legal/page', [
            'title' => 'Kebijakan Cookie - AdaAcara',
            'eyebrow' => 'Cookie',
            'heading' => 'Kebijakan Cookie',
            'description' => 'Informasi tentang penggunaan cookie, session, dan teknologi serupa di AdaAcara.',
            'updatedAt' => '14 Juni 2026',
            'sections' => [
                [
                    'title' => '1. Apa Itu Cookie',
                    'body' => [
                        'Cookie adalah file kecil yang disimpan di browser untuk membantu website mengenali sesi, preferensi, dan aktivitas tertentu.',
                        'AdaAcara juga dapat menggunakan session server-side dan penyimpanan lokal browser untuk menjaga pengalaman pengguna tetap konsisten.',
                    ],
                ],
                [
                    'title' => '2. Cookie yang Diperlukan',
                    'body' => [
                        'Cookie/session diperlukan untuk login, menjaga sesi pengguna, melindungi form dari CSRF, menyimpan status keamanan, dan memastikan fitur dasar berjalan.',
                        'Tanpa cookie yang diperlukan, sebagian fitur seperti login, dashboard, checkout, editor, atau publish mungkin tidak berfungsi.',
                    ],
                ],
                [
                    'title' => '3. Cookie Preferensi dan Fungsional',
                    'body' => [
                        'Kami dapat menyimpan preferensi tampilan, status editor, filter, atau informasi sementara agar pengalaman penggunaan lebih nyaman.',
                        'Beberapa fitur editor atau preview dapat memakai penyimpanan lokal browser untuk performa dan pemulihan tampilan.',
                    ],
                ],
                [
                    'title' => '4. Cookie Pihak Ketiga',
                    'body' => [
                        'Fitur seperti Google login, pembayaran, atau layanan email dapat menggunakan cookie atau teknologi serupa dari penyedia pihak ketiga.',
                        'Penggunaan data oleh pihak ketiga mengikuti kebijakan privasi dan cookie masing-masing penyedia.',
                    ],
                ],
                [
                    'title' => '5. Pengaturan Cookie',
                    'body' => [
                        'Kamu dapat mengatur atau menghapus cookie melalui pengaturan browser. Namun, menonaktifkan cookie tertentu dapat membuat beberapa fitur AdaAcara tidak berjalan normal.',
                        'Dengan tetap menggunakan AdaAcara, kamu memahami bahwa cookie yang diperlukan dapat dipakai untuk menjalankan layanan inti.',
                    ],
                ],
                [
                    'title' => '6. Kontak',
                    'body' => [
                        'Pertanyaan tentang cookie dapat dikirim ke halo@adaacara.com.',
                    ],
                ],
            ],
        ]);
    }
}
