<?php

namespace App\Libraries;

class GuideArticleCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function articles(): array
    {
        return [
            'cara-membuat-undangan-digital' => [
                'slug' => 'cara-membuat-undangan-digital',
                'category' => 'Panduan Undangan Digital',
                'title' => 'Cara Membuat Undangan Digital yang Rapi dan Mudah Dibagikan',
                'seo_title' => 'Cara Membuat Undangan Digital Online - Panduan AdaAcara',
                'description' => 'Panduan praktis membuat undangan digital online dari memilih template, mengisi detail acara, mengatur RSVP, sampai publish link undangan.',
                'updated_at' => '2026-06-28',
                'reading_time' => '5 menit',
                'intro' => 'Undangan digital yang bagus tidak harus rumit. Yang penting tamu langsung paham acaranya apa, kapan, di mana, dan bagaimana cara memberi konfirmasi.',
                'cta' => [
                    'label' => 'Mulai dari Template',
                    'url' => 'templates',
                ],
                'related' => ['undangan-digital', 'template-undangan', 'editor-undangan-digital'],
                'sections' => [
                    [
                        'heading' => 'Mulai dari informasi acara yang paling penting',
                        'body' => [
                            'Sebelum memilih desain, tulis dulu nama acara, tanggal, waktu, lokasi, nama penyelenggara, dan nomor kontak. Ini terdengar sederhana, tapi bagian inilah yang paling sering membuat undangan terasa berantakan kalau disusun belakangan.',
                            'Untuk acara pernikahan, pastikan nama pasangan, akad, resepsi, lokasi, dan dress code sudah jelas. Untuk seminar atau corporate event, pastikan topik, pembicara, jadwal, dan link pendaftaran tidak tenggelam di bawah dekorasi.',
                        ],
                    ],
                    [
                        'heading' => 'Pilih template yang sesuai suasana acara',
                        'body' => [
                            'Template minimalis cocok untuk acara modern dan corporate. Template floral atau elegant cocok untuk wedding. Template yang lebih ceria bisa dipakai untuk ulang tahun, khitan, atau aqiqah.',
                            'Jangan memilih template hanya karena terlihat ramai. Pilih yang membuat informasi utama mudah dibaca di layar ponsel.',
                        ],
                    ],
                    [
                        'heading' => 'Atur alur halaman dari atas ke bawah',
                        'body' => [
                            'Urutan yang aman biasanya: pembuka, nama acara, tanggal, lokasi, detail jadwal, galeri atau story, RSVP, guestbook, lalu penutup. Dengan alur seperti ini, tamu tidak perlu menebak-nebak.',
                            'Kalau acaranya formal, gunakan teks yang singkat dan jelas. Kalau acaranya santai, kamu bisa menambahkan story atau quotes agar undangan terasa lebih personal.',
                        ],
                    ],
                    [
                        'heading' => 'Publish dan cek ulang dari HP',
                        'body' => [
                            'Setelah undangan dipublish, buka link dari ponsel. Cek ukuran teks, tombol RSVP, map, musik, dan foto. Jika ada bagian yang terlalu kecil atau terlalu panjang, kembali ke editor dan rapikan sebelum link dibagikan.',
                        ],
                    ],
                ],
                'checklist' => [
                    'Nama acara dan nama penyelenggara sudah benar.',
                    'Tanggal, waktu, dan lokasi tidak salah ketik.',
                    'Tombol RSVP atau kontak mudah ditemukan.',
                    'Foto tidak pecah dan tidak terlalu berat.',
                    'Link public sudah dicoba dari ponsel.',
                ],
            ],
            'contoh-kata-kata-undangan-pernikahan' => [
                'slug' => 'contoh-kata-kata-undangan-pernikahan',
                'category' => 'Contoh Kata-Kata',
                'title' => 'Contoh Kata-Kata Undangan Pernikahan Digital yang Natural',
                'seo_title' => 'Contoh Kata-Kata Undangan Pernikahan Digital - AdaAcara',
                'description' => 'Kumpulan contoh kata-kata undangan pernikahan digital yang sopan, hangat, dan mudah disesuaikan untuk undangan online.',
                'updated_at' => '2026-06-28',
                'reading_time' => '4 menit',
                'intro' => 'Kata-kata undangan tidak perlu terlalu panjang. Yang penting sopan, hangat, dan memberi informasi yang jelas untuk tamu.',
                'cta' => [
                    'label' => 'Pilih Template Wedding',
                    'url' => 'undangan-pernikahan-digital',
                ],
                'related' => ['undangan-pernikahan-digital', 'template-undangan', 'undangan-digital'],
                'sections' => [
                    [
                        'heading' => 'Pembuka yang sopan dan aman dipakai',
                        'body' => [
                            'Dengan penuh rasa syukur, kami bermaksud mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa restu pada acara pernikahan kami.',
                            'Merupakan kehormatan dan kebahagiaan bagi kami apabila Bapak/Ibu/Saudara/i berkenan hadir pada hari bahagia kami.',
                        ],
                    ],
                    [
                        'heading' => 'Versi singkat untuk undangan modern',
                        'body' => [
                            'Kami mengundang Anda untuk merayakan hari bahagia kami. Kehadiran dan doa Anda akan menjadi bagian yang berarti dalam perjalanan baru kami.',
                            'Bersama keluarga, kami mengundang Anda untuk hadir dalam perayaan pernikahan kami.',
                        ],
                    ],
                    [
                        'heading' => 'Penutup yang hangat',
                        'body' => [
                            'Atas kehadiran dan doa restunya, kami mengucapkan terima kasih.',
                            'Semoga langkah baru ini menjadi awal yang baik, penuh kasih, dan selalu diberkahi.',
                        ],
                    ],
                ],
                'checklist' => [
                    'Gunakan nama tamu jika ingin undangan terasa personal.',
                    'Hindari kalimat terlalu panjang di bagian hero.',
                    'Pisahkan teks doa, jadwal, dan lokasi agar mudah dibaca.',
                ],
            ],
            'cara-membuat-rsvp-online' => [
                'slug' => 'cara-membuat-rsvp-online',
                'category' => 'RSVP dan Tamu',
                'title' => 'Cara Membuat RSVP Online agar Konfirmasi Tamu Lebih Rapi',
                'seo_title' => 'Cara Membuat RSVP Online untuk Undangan Digital - AdaAcara',
                'description' => 'Pelajari cara memakai RSVP online pada undangan digital agar tamu bisa memberi konfirmasi hadir, tidak hadir, atau ragu dengan lebih mudah.',
                'updated_at' => '2026-06-28',
                'reading_time' => '4 menit',
                'intro' => 'RSVP membantu kamu memperkirakan jumlah tamu. Ini berguna untuk konsumsi, kursi, souvenir, dan koordinasi acara.',
                'cta' => [
                    'label' => 'Buat Undangan dengan RSVP',
                    'url' => 'undangan-digital',
                ],
                'related' => ['undangan-digital', 'editor-undangan-digital', 'templates'],
                'sections' => [
                    [
                        'heading' => 'Letakkan RSVP di bagian yang mudah ditemukan',
                        'body' => [
                            'Jangan sembunyikan RSVP terlalu bawah jika acara membutuhkan kepastian jumlah tamu. Letakkan setelah detail jadwal atau sebelum guestbook.',
                            'Gunakan label yang jelas seperti Konfirmasi Kehadiran, RSVP, atau Saya Akan Hadir.',
                        ],
                    ],
                    [
                        'heading' => 'Minta data seperlunya saja',
                        'body' => [
                            'Nama dan status kehadiran biasanya sudah cukup. Jika perlu, tambahkan jumlah tamu atau catatan singkat, tapi jangan membuat form terasa panjang.',
                        ],
                    ],
                    [
                        'heading' => 'Cek hasil RSVP dari dashboard',
                        'body' => [
                            'Setelah undangan dibagikan, pantau respons tamu secara berkala. Jika banyak yang belum mengisi, kirim pengingat yang sopan melalui WhatsApp.',
                        ],
                    ],
                ],
                'checklist' => [
                    'Tombol RSVP mudah terlihat.',
                    'Pilihan hadir/tidak hadir/ragu jelas.',
                    'Form tidak terlalu panjang.',
                    'Link undangan sudah dites setelah RSVP aktif.',
                ],
            ],
            'template-undangan-wedding-elegan' => [
                'slug' => 'template-undangan-wedding-elegan',
                'category' => 'Inspirasi Template',
                'title' => 'Cara Memilih Template Undangan Wedding yang Elegan',
                'seo_title' => 'Template Undangan Wedding Elegan - Tips Memilih Desain',
                'description' => 'Tips memilih template undangan wedding elegan agar desain terlihat rapi, mudah dibaca, dan sesuai dengan suasana acara.',
                'updated_at' => '2026-06-28',
                'reading_time' => '5 menit',
                'intro' => 'Template wedding yang elegan biasanya tidak berteriak. Ia memberi ruang untuk nama pasangan, tanggal, dan suasana acara tampil dengan tenang.',
                'cta' => [
                    'label' => 'Lihat Template Wedding',
                    'url' => 'undangan-pernikahan-digital',
                ],
                'related' => ['undangan-pernikahan-digital', 'template-undangan', 'templates'],
                'sections' => [
                    [
                        'heading' => 'Pilih warna yang tidak melelahkan mata',
                        'body' => [
                            'Warna putih, ivory, gold, hijau zaitun, navy, atau dusty rose sering terasa aman untuk wedding. Hindari kombinasi terlalu kontras jika teks utama menjadi sulit dibaca.',
                        ],
                    ],
                    [
                        'heading' => 'Jangan terlalu banyak font',
                        'body' => [
                            'Dua font biasanya cukup: satu untuk nama pasangan, satu untuk informasi acara. Jika terlalu banyak jenis font, undangan cepat terlihat kurang rapi.',
                        ],
                    ],
                    [
                        'heading' => 'Pastikan bagian jadwal tetap jelas',
                        'body' => [
                            'Nama pasangan boleh besar dan dekoratif, tapi jadwal, lokasi, dan tombol map harus tetap mudah dibaca. Elegan bukan berarti semua teks harus tipis dan kecil.',
                        ],
                    ],
                ],
                'checklist' => [
                    'Nama pasangan menjadi fokus utama.',
                    'Jadwal dan lokasi mudah dibaca.',
                    'Foto tidak menutupi teks penting.',
                    'Warna tombol CTA tetap terlihat.',
                ],
            ],
            'cara-menulis-detail-acara' => [
                'slug' => 'cara-menulis-detail-acara',
                'category' => 'Isi Undangan',
                'title' => 'Cara Menulis Detail Acara agar Tamu Tidak Bingung',
                'seo_title' => 'Cara Menulis Detail Acara di Undangan Digital - AdaAcara',
                'description' => 'Panduan menulis detail acara pada undangan digital agar tanggal, waktu, lokasi, dress code, dan kontak terlihat jelas.',
                'updated_at' => '2026-06-28',
                'reading_time' => '4 menit',
                'intro' => 'Desain boleh cantik, tapi undangan tetap harus menjawab pertanyaan tamu: kapan, di mana, harus memakai apa, dan perlu konfirmasi ke siapa.',
                'cta' => [
                    'label' => 'Buat Undangan Online',
                    'url' => 'undangan-digital',
                ],
                'related' => ['undangan-digital', 'editor-undangan-digital', 'cara-membuat-rsvp-online'],
                'sections' => [
                    [
                        'heading' => 'Pisahkan tanggal dan jam',
                        'body' => [
                            'Tulis tanggal dalam format yang mudah dibaca, misalnya Sabtu, 12 Oktober 2026. Untuk jam, gunakan format yang konsisten seperti 09.00 WIB atau 09:00 WIB.',
                        ],
                    ],
                    [
                        'heading' => 'Buat lokasi mudah ditindaklanjuti',
                        'body' => [
                            'Nama gedung atau tempat saja kadang tidak cukup. Tambahkan alamat singkat dan tombol map agar tamu tidak perlu mencari manual.',
                        ],
                    ],
                    [
                        'heading' => 'Tulis catatan tambahan dengan ringkas',
                        'body' => [
                            'Dress code, parkir, sesi acara, atau batas konfirmasi sebaiknya ditulis dalam kalimat pendek. Kalau terlalu panjang, tamu biasanya melewatkannya.',
                        ],
                    ],
                ],
                'checklist' => [
                    'Tanggal ditulis lengkap.',
                    'Jam memakai zona waktu jika perlu.',
                    'Alamat dan map tersedia.',
                    'Dress code atau catatan khusus tidak tersembunyi.',
                ],
            ],
            'cara-pakai-magic-layer' => [
                'slug' => 'cara-pakai-magic-layer',
                'category' => 'Fitur AdaAcara',
                'title' => 'Cara Pakai Magic Layer untuk Membaca Teks dari Gambar',
                'seo_title' => 'Cara Pakai Magic Layer - OCR Gambar Undangan di AdaAcara',
                'description' => 'Panduan memakai Magic Layer untuk memproses gambar referensi, membaca teks dengan OCR, dan memisahkan elemen gambar di editor AdaAcara.',
                'updated_at' => '2026-06-28',
                'reading_time' => '5 menit',
                'intro' => 'Magic Layer membantu saat kamu punya gambar referensi undangan dan ingin mengambil teks atau objek visualnya agar lebih mudah diedit.',
                'cta' => [
                    'label' => 'Pelajari Magic Layer',
                    'url' => 'fitur/magic-layer',
                ],
                'related' => ['fitur/magic-layer', 'fitur/remove-bg', 'editor-undangan-digital'],
                'sections' => [
                    [
                        'heading' => 'Gunakan gambar yang jelas',
                        'body' => [
                            'Hasil terbaik biasanya datang dari gambar yang tidak buram, tidak terlalu kecil, dan punya kontras cukup antara teks dan background.',
                            'Jika teks terlalu tipis atau dekorasinya terlalu ramai, hasil OCR bisa perlu dirapikan lagi secara manual.',
                        ],
                    ],
                    [
                        'heading' => 'Cek ulang teks setelah diproses',
                        'body' => [
                            'Magic Layer bisa membantu membaca teks, tapi nama, tanggal, dan alamat tetap wajib dicek ulang. Jangan langsung publish tanpa memastikan isi benar.',
                        ],
                    ],
                    [
                        'heading' => 'Rapikan posisi di canvas',
                        'body' => [
                            'Setelah hasil masuk ke canvas, sesuaikan lagi ukuran font, jarak antar teks, dan posisi elemen. Anggap hasil Magic Layer sebagai titik awal yang mempercepat, bukan hasil final yang harus diterima begitu saja.',
                        ],
                    ],
                ],
                'checklist' => [
                    'Gambar tidak buram.',
                    'Teks kontras dengan background.',
                    'Nama dan tanggal sudah dicek ulang.',
                    'Hasil gambar dan teks dirapikan di canvas.',
                ],
            ],
            'cara-hapus-background-foto-undangan' => [
                'slug' => 'cara-hapus-background-foto-undangan',
                'category' => 'Fitur AdaAcara',
                'title' => 'Cara Hapus Background Foto untuk Desain Undangan',
                'seo_title' => 'Cara Hapus Background Foto Undangan - Remove BG AdaAcara',
                'description' => 'Panduan memakai Remove BG untuk membersihkan foto atau ornamen agar lebih mudah dipakai dalam desain undangan digital.',
                'updated_at' => '2026-06-28',
                'reading_time' => '4 menit',
                'intro' => 'Foto dengan background yang bersih membuat desain undangan terasa lebih rapi. Remove BG membantu memisahkan objek dari latarnya.',
                'cta' => [
                    'label' => 'Pelajari Remove BG',
                    'url' => 'fitur/remove-bg',
                ],
                'related' => ['fitur/remove-bg', 'fitur/magic-layer', 'editor-undangan-digital'],
                'sections' => [
                    [
                        'heading' => 'Pilih foto dengan subjek yang jelas',
                        'body' => [
                            'Remove background bekerja lebih baik jika subjek foto terlihat jelas dan tidak terlalu menyatu dengan background. Foto yang terlalu gelap atau ramai biasanya perlu perapian manual.',
                        ],
                    ],
                    [
                        'heading' => 'Gunakan hasil transparan sebagai elemen desain',
                        'body' => [
                            'Setelah background dihapus, letakkan foto di atas shape, frame, atau background undangan. Pastikan pinggir objek tidak terlalu kasar saat diperbesar.',
                        ],
                    ],
                    [
                        'heading' => 'Jangan lupa kompres foto',
                        'body' => [
                            'Foto yang terlalu besar bisa membuat halaman undangan lebih berat. Gunakan ukuran secukupnya agar loading tetap nyaman untuk tamu.',
                        ],
                    ],
                ],
                'checklist' => [
                    'Subjek foto terlihat jelas.',
                    'Pinggir hasil remove bg tidak mengganggu.',
                    'Ukuran foto tidak terlalu berat.',
                ],
            ],
            'contoh-kata-kata-undangan-aqiqah' => [
                'slug' => 'contoh-kata-kata-undangan-aqiqah',
                'category' => 'Contoh Kata-Kata',
                'title' => 'Contoh Kata-Kata Undangan Aqiqah yang Sopan dan Hangat',
                'seo_title' => 'Contoh Kata-Kata Undangan Aqiqah Digital - AdaAcara',
                'description' => 'Contoh kalimat undangan aqiqah yang sopan, hangat, dan mudah disesuaikan untuk undangan digital keluarga.',
                'updated_at' => '2026-06-28',
                'reading_time' => '4 menit',
                'intro' => 'Undangan aqiqah biasanya terasa paling baik saat bahasanya sederhana, penuh syukur, dan tidak terlalu formal.',
                'cta' => [
                    'label' => 'Lihat Template Undangan',
                    'url' => 'template-undangan',
                ],
                'related' => ['template-undangan', 'undangan-digital', 'cara-menulis-detail-acara'],
                'sections' => [
                    [
                        'heading' => 'Pembuka undangan aqiqah',
                        'body' => [
                            'Dengan memohon rahmat dan ridha Allah SWT, kami bermaksud mengundang Bapak/Ibu/Saudara/i untuk hadir dalam acara aqiqah putra/putri kami.',
                            'Sebagai ungkapan syukur atas kelahiran buah hati kami, dengan hormat kami mengundang Bapak/Ibu/Saudara/i untuk hadir dan memberikan doa.',
                        ],
                    ],
                    [
                        'heading' => 'Kalimat singkat untuk keluarga dan teman',
                        'body' => [
                            'Kami mengundang keluarga dan sahabat untuk hadir dalam acara aqiqah buah hati kami. Doa dan kehadiran Anda sangat berarti bagi keluarga kecil kami.',
                        ],
                    ],
                    [
                        'heading' => 'Penutup yang aman dipakai',
                        'body' => [
                            'Atas kehadiran dan doa yang diberikan, kami mengucapkan terima kasih. Semoga Allah SWT membalas dengan kebaikan.',
                        ],
                    ],
                ],
                'checklist' => [
                    'Nama anak ditulis jelas.',
                    'Nama orang tua tidak salah.',
                    'Tanggal, jam, dan alamat mudah ditemukan.',
                ],
            ],
            'tips-jual-template-undangan-digital' => [
                'slug' => 'tips-jual-template-undangan-digital',
                'category' => 'Tips Kreator',
                'title' => 'Tips Menjual Template Undangan Digital agar Lebih Mudah Dipilih',
                'seo_title' => 'Tips Jual Template Undangan Digital untuk Kreator - AdaAcara',
                'description' => 'Panduan kreator untuk membuat template undangan digital yang rapi, mudah diedit, dan lebih menarik bagi calon pengguna.',
                'updated_at' => '2026-06-28',
                'reading_time' => '5 menit',
                'intro' => 'Template yang laku biasanya bukan hanya cantik. Ia juga mudah dipakai, mudah diedit, dan membuat pembeli cepat membayangkan acaranya sendiri.',
                'cta' => [
                    'label' => 'Daftar Kreator',
                    'url' => 'creator/apply',
                ],
                'related' => ['template-undangan', 'editor-undangan-digital', 'templates'],
                'sections' => [
                    [
                        'heading' => 'Buat struktur yang mudah diganti',
                        'body' => [
                            'Pisahkan teks utama, tanggal, lokasi, dan ornamen agar pengguna bisa mengedit tanpa merusak layout. Jangan mengunci semua informasi penting dalam satu gambar.',
                        ],
                    ],
                    [
                        'heading' => 'Tunjukkan karakter desain dari thumbnail',
                        'body' => [
                            'Thumbnail adalah pintu pertama. Gunakan preview yang bersih, kontras, dan langsung menunjukkan gaya template: elegant, modern, islami, rustic, floral, atau playful.',
                        ],
                    ],
                    [
                        'heading' => 'Jangan terlalu ramai',
                        'body' => [
                            'Template yang terlalu penuh bisa terlihat menarik saat pertama dilihat, tapi sulit dipakai untuk banyak acara. Sisakan ruang agar nama, tanggal, dan foto pengguna tetap menonjol.',
                        ],
                    ],
                ],
                'checklist' => [
                    'Teks penting bukan bagian dari gambar mati.',
                    'Thumbnail jelas dan tidak terlalu gelap.',
                    'Template tetap rapi saat nama diganti lebih panjang.',
                    'Warna dan font konsisten.',
                ],
            ],
            'checklist-sebelum-publish-undangan' => [
                'slug' => 'checklist-sebelum-publish-undangan',
                'category' => 'Checklist',
                'title' => 'Checklist Sebelum Publish Undangan Digital',
                'seo_title' => 'Checklist Sebelum Publish Undangan Digital - AdaAcara',
                'description' => 'Daftar cek sebelum publish undangan digital agar nama, tanggal, lokasi, RSVP, foto, musik, dan link publik tidak bermasalah.',
                'updated_at' => '2026-06-28',
                'reading_time' => '4 menit',
                'intro' => 'Sebelum link dibagikan ke banyak orang, luangkan waktu beberapa menit untuk mengecek hal-hal kecil yang sering terlewat.',
                'cta' => [
                    'label' => 'Buka Template',
                    'url' => 'templates',
                ],
                'related' => ['undangan-digital', 'cara-menulis-detail-acara', 'cara-membuat-rsvp-online'],
                'sections' => [
                    [
                        'heading' => 'Cek isi paling sensitif',
                        'body' => [
                            'Nama, tanggal, waktu, alamat, dan nomor kontak adalah bagian yang paling tidak boleh salah. Baca ulang pelan-pelan, lalu minta satu orang lain ikut mengecek.',
                        ],
                    ],
                    [
                        'heading' => 'Cek tampilan dari ponsel',
                        'body' => [
                            'Buka link preview dari ponsel. Pastikan teks tidak terlalu kecil, tombol bisa diklik, gambar tidak pecah, dan halaman tidak terasa terlalu berat.',
                        ],
                    ],
                    [
                        'heading' => 'Cek fitur interaktif',
                        'body' => [
                            'Isi RSVP percobaan, kirim guestbook percobaan, buka link map, dan cek musik jika digunakan. Setelah yakin, baru bagikan link publik.',
                        ],
                    ],
                ],
                'checklist' => [
                    'Nama dan gelar benar.',
                    'Tanggal dan jam benar.',
                    'Lokasi dan tombol map aktif.',
                    'RSVP berhasil dikirim.',
                    'Guestbook tampil sesuai harapan.',
                    'Link public sudah dicoba di ponsel.',
                ],
            ],
        ];
    }

    public static function find(string $slug): ?array
    {
        $slug = trim($slug, '/');
        $articles = self::articles();

        return $articles[$slug] ?? null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function all(): array
    {
        return array_values(self::articles());
    }
}
