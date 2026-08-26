<?php

namespace App\Libraries;

class SeoLandingPageCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function pages(): array
    {
        return [
            'undangan-digital' => [
                'path' => 'undangan-digital',
                'eyebrow' => 'Undangan Digital',
                'title' => 'Undangan Digital Online untuk Semua Acara',
                'seo_title' => 'Undangan Digital Online - Buat Website Acara di AdaAcara',
                'description' => 'Buat undangan digital online untuk wedding, aqiqah, khitan, ulang tahun, seminar, dan acara corporate dengan editor visual, RSVP, ucapan tamu, musik, dan link publik.',
                'hero' => 'Buat undangan digital yang siap dibagikan sebagai website acara.',
                'intro' => 'AdaAcara membantu kamu membuat halaman undangan digital dari template atau canvas kosong, lalu mempublishnya menjadi link acara yang mudah dikirim ke tamu.',
                'keywords' => ['undangan digital', 'undangan online', 'website undangan', 'RSVP online', 'ucapan tamu'],
                'sections' => [
                    ['title' => 'Cocok untuk banyak jenis acara', 'body' => 'Gunakan AdaAcara untuk pernikahan, aqiqah, khitan, ulang tahun, bukber, halal bihalal, seminar, gathering, launching, dan acara keluarga.'],
                    ['title' => 'Editor visual yang mudah dipakai', 'body' => 'Atur teks, foto, warna, musik, halaman, animasi, link, dan elemen acara langsung dari editor visual.'],
                    ['title' => 'Publish menjadi link publik', 'body' => 'Setelah selesai, undangan dapat dipublish menjadi URL public yang siap dibagikan melalui WhatsApp, Instagram, atau media sosial.'],
                ],
                'faqs' => [
                    ['Apakah bisa membuat undangan selain wedding?', 'Bisa. AdaAcara mendukung wedding, seminar, bukber, halal bihalal, ulang tahun, khitan, aqiqah, dan acara corporate.'],
                    ['Apakah perlu skill desain?', 'Tidak wajib. Kamu bisa mulai dari template yang tersedia lalu menyesuaikan teks, foto, warna, dan halaman.'],
                    ['Apakah undangan bisa dipublish sebagai website?', 'Bisa. Undangan dapat dipublish sebagai link publik yang bisa dibuka oleh tamu.'],
                ],
            ],
            'undangan-pernikahan-digital' => [
                'path' => 'undangan-pernikahan-digital',
                'eyebrow' => 'Wedding',
                'title' => 'Undangan Pernikahan Digital yang Elegan dan Interaktif',
                'seo_title' => 'Undangan Pernikahan Digital - Template Wedding Online AdaAcara',
                'description' => 'Buat undangan pernikahan digital dengan template wedding, RSVP, ucapan tamu, musik, galeri foto, amplop digital, dan link undangan online.',
                'hero' => 'Rancang undangan wedding online yang terasa personal.',
                'intro' => 'Mulai dari template wedding lalu sesuaikan nama pasangan, tanggal, lokasi, foto, cerita, RSVP, dan detail acara dalam satu halaman undangan digital.',
                'keywords' => ['undangan pernikahan digital', 'template wedding', 'undangan website wedding', 'RSVP wedding'],
                'sections' => [
                    ['title' => 'Template wedding siap edit', 'body' => 'Pilih desain wedding, ganti nama pasangan, tanggal, lokasi, foto, dan detail acara tanpa membuat layout dari nol.'],
                    ['title' => 'Fitur lengkap untuk tamu', 'body' => 'Tambahkan RSVP, guestbook, ucapan, musik, countdown, story, gift, dan link lokasi agar tamu mendapat informasi yang jelas.'],
                    ['title' => 'Mudah dibagikan', 'body' => 'Setelah publish, undangan bisa dibagikan sebagai link melalui WhatsApp dan media sosial.'],
                ],
                'faqs' => [
                    ['Apakah bisa pakai foto sendiri?', 'Bisa. Kamu dapat upload foto pasangan, galeri, background, dan elemen gambar lain di editor.'],
                    ['Apakah bisa ada RSVP?', 'Bisa. AdaAcara mendukung RSVP dan guestbook untuk membantu memantau respons tamu.'],
                    ['Apakah desain bisa diedit setelah publish?', 'Bisa. Kamu dapat kembali ke editor untuk memperbaiki isi lalu menyimpan perubahan.'],
                ],
            ],
            'template-undangan' => [
                'path' => 'template-undangan',
                'eyebrow' => 'Template',
                'title' => 'Template Undangan Digital Siap Pakai',
                'seo_title' => 'Template Undangan Digital - Wedding, Aqiqah, Khitan, Seminar',
                'description' => 'Pilih template undangan digital AdaAcara untuk wedding, aqiqah, khitan, ulang tahun, seminar, bukber, halal bihalal, dan acara corporate.',
                'hero' => 'Mulai lebih cepat dari template undangan yang siap diedit.',
                'intro' => 'Template AdaAcara dirancang untuk mempercepat proses membuat undangan digital. Kamu tinggal memilih desain, mengisi detail acara, lalu menyesuaikan tampilannya.',
                'keywords' => ['template undangan', 'template undangan digital', 'template wedding online', 'template acara'],
                'sections' => [
                    ['title' => 'Banyak kategori acara', 'body' => 'Gunakan template untuk wedding, aqiqah, khitan, ulang tahun, seminar, bukber, halal bihalal, dan corporate event.'],
                    ['title' => 'Bisa disesuaikan di editor', 'body' => 'Setiap template dapat diedit dari sisi teks, foto, warna, font, halaman, musik, animasi, dan elemen interaktif.'],
                    ['title' => 'Cocok untuk kreator', 'body' => 'Kreator dapat membuat template sendiri dan mengajukannya untuk tampil di marketplace AdaAcara.'],
                ],
                'faqs' => [
                    ['Apakah template bisa diedit?', 'Bisa. Template adalah desain awal yang dapat disesuaikan lewat editor visual AdaAcara.'],
                    ['Apakah ada template gratis?', 'Ada. Template gratis dapat dipakai sesuai ketentuan akses yang tersedia.'],
                    ['Apakah kreator bisa menjual template?', 'Bisa. Kreator dapat mengajukan template untuk direview dan dipublikasikan.'],
                ],
            ],
            'editor-undangan-digital' => [
                'path' => 'editor-undangan-digital',
                'eyebrow' => 'Editor',
                'title' => 'Editor Undangan Digital Visual untuk Membuat Website Acara',
                'seo_title' => 'Editor Undangan Digital - Edit Template dan Publish Website Acara',
                'description' => 'Edit undangan digital secara visual dengan teks, foto, halaman, animasi, musik, RSVP, guestbook, dan fitur AI di AdaAcara Design Studio.',
                'hero' => 'Edit desain undangan langsung dari canvas visual.',
                'intro' => 'AdaAcara Design Studio dibuat untuk membantu pengguna mengubah template menjadi undangan digital yang personal tanpa harus menulis kode.',
                'keywords' => ['editor undangan digital', 'editor undangan online', 'buat undangan online', 'design studio undangan'],
                'sections' => [
                    ['title' => 'Canvas visual', 'body' => 'Atur elemen teks, gambar, shape, halaman, warna, font, dan posisi langsung di canvas editor.'],
                    ['title' => 'Fitur interaktif', 'body' => 'Tambahkan RSVP, guestbook, musik, countdown, link, social media, story, quotes, dan elemen acara lain.'],
                    ['title' => 'Bantuan AI', 'body' => 'Gunakan AdaAcara AI, Remove BG, dan Magic Layer untuk mempercepat proses desain dan pengolahan gambar.'],
                ],
                'faqs' => [
                    ['Apakah editornya seperti Canva?', 'Editor AdaAcara mendukung canvas visual, drag, resize, layer, halaman, teks, gambar, dan elemen desain.'],
                    ['Apakah editor bisa dipakai di mobile?', 'Dukungan mobile sedang dikembangkan. Untuk stabilitas canvas, editor disarankan dibuka dari laptop atau PC.'],
                    ['Apakah bisa publish setelah edit?', 'Bisa. Setelah desain selesai, halaman dapat dipublish menjadi website undangan.'],
                ],
            ],
            'fitur/acara-ai' => [
                'path' => 'fitur/acara-ai',
                'eyebrow' => 'AdaAcara AI',
                'title' => 'AdaAcara AI untuk Membantu Membuat Desain Undangan',
                'seo_title' => 'AdaAcara AI - AI untuk Desain Undangan Digital',
                'description' => 'AdaAcara AI membantu membuat dan mengubah desain undangan digital dari prompt, referensi halaman, dan kebutuhan acara di editor AdaAcara.',
                'hero' => 'Gunakan AI untuk mempercepat proses membuat undangan digital.',
                'intro' => 'AdaAcara AI dirancang sebagai asisten desain di editor, membantu membuat rancangan halaman, menyusun elemen, dan mempercepat eksplorasi visual.',
                'keywords' => ['AI undangan digital', 'AI desain undangan', 'AdaAcara AI', 'buat undangan dengan AI'],
                'sections' => [
                    ['title' => 'Prompt kreatif', 'body' => 'Berikan instruksi desain seperti redesign halaman, tambah background, atau ubah gaya visual sesuai kebutuhan acara.'],
                    ['title' => 'Terhubung dengan editor', 'body' => 'Hasil AI masuk ke canvas editor agar tetap bisa diperiksa, diedit, dan disesuaikan secara manual.'],
                    ['title' => 'Tetap perlu review', 'body' => 'Hasil AI dapat membantu mempercepat, tetapi teks, posisi, dan detail acara tetap perlu dicek sebelum publish.'],
                ],
                'faqs' => [
                    ['Apakah hasil AI langsung bisa dipublish?', 'Sebaiknya hasil AI tetap dicek dulu, terutama teks, tanggal, nama, dan posisi elemen.'],
                    ['Apakah AI bisa mengedit halaman yang sudah ada?', 'AdaAcara AI dirancang untuk membantu perubahan halaman dari konteks desain yang sedang aktif.'],
                    ['Apakah fitur AI termasuk premium?', 'Akses fitur AI dapat mengikuti paket atau kebijakan fitur premium AdaAcara.'],
                ],
            ],
            'fitur/magic-layer' => [
                'path' => 'fitur/magic-layer',
                'eyebrow' => 'Magic Layer',
                'title' => 'Magic Layer untuk Memecah Gambar dan Teks Undangan',
                'seo_title' => 'Magic Layer - OCR dan Remove Background untuk Undangan Digital',
                'description' => 'Magic Layer membantu memproses gambar referensi undangan dengan OCR, remove background, dan pemisahan elemen agar teks dan gambar lebih mudah diedit.',
                'hero' => 'Ubah gambar referensi menjadi elemen yang lebih mudah diedit.',
                'intro' => 'Magic Layer menggabungkan OCR dan remove background untuk membantu membaca teks pada gambar serta memproses bagian visual yang relevan ke canvas editor.',
                'keywords' => ['Magic Layer', 'OCR undangan', 'scan teks gambar', 'remove background undangan'],
                'sections' => [
                    ['title' => 'OCR teks pada gambar', 'body' => 'AI membaca teks yang terlihat pada gambar dan mencoba menempatkannya sebagai elemen teks di canvas.'],
                    ['title' => 'Remove background', 'body' => 'Bagian gambar dapat diproses agar background lebih bersih dan mudah digunakan dalam desain.'],
                    ['title' => 'Tetap bisa diedit manual', 'body' => 'Setelah diproses, hasil Magic Layer tetap dapat disesuaikan di editor agar lebih rapi dan akurat.'],
                ],
                'faqs' => [
                    ['Apakah Magic Layer selalu akurat?', 'Tidak selalu. Hasil OCR dan pemisahan gambar dapat dipengaruhi kualitas gambar, kontras, font, dan layout.'],
                    ['Apakah teks hasil OCR bisa diedit?', 'Teks yang berhasil dibaca dapat menjadi elemen teks yang bisa disesuaikan di editor.'],
                    ['Apakah gambar asli tetap perlu dicek?', 'Ya. Selalu cek ulang posisi teks, font, dan hasil gambar sebelum menyimpan atau publish.'],
                ],
            ],
            'fitur/remove-bg' => [
                'path' => 'fitur/remove-bg',
                'eyebrow' => 'Remove BG',
                'title' => 'Remove BG untuk Gambar Undangan Digital',
                'seo_title' => 'Remove BG Undangan - Hapus Background Gambar di AdaAcara',
                'description' => 'Fitur Remove BG membantu menghapus background gambar untuk desain undangan digital agar foto, ornamen, dan objek visual lebih mudah dipakai.',
                'hero' => 'Hapus background gambar langsung dari editor undangan.',
                'intro' => 'Remove BG membantu membersihkan gambar yang ingin dipakai sebagai elemen desain, sehingga hasil undangan digital terlihat lebih rapi.',
                'keywords' => ['remove bg undangan', 'hapus background gambar', 'background remover undangan', 'edit foto undangan'],
                'sections' => [
                    ['title' => 'Cocok untuk foto dan ornamen', 'body' => 'Gunakan untuk memproses foto, dekorasi, ornamen, atau elemen visual sebelum ditempatkan di canvas.'],
                    ['title' => 'Terintegrasi dengan editor', 'body' => 'Hasil remove background dapat langsung masuk ke alur desain AdaAcara.'],
                    ['title' => 'Bisa digabung dengan Magic Layer', 'body' => 'Untuk kebutuhan tertentu, remove background dapat digunakan bersama OCR dan proses pemisahan layer.'],
                ],
                'faqs' => [
                    ['Apakah Remove BG bekerja untuk semua gambar?', 'Hasil terbaik biasanya muncul pada gambar dengan subjek yang jelas dan kontras yang baik.'],
                    ['Apakah hasilnya bisa diedit lagi?', 'Bisa. Hasil gambar dapat diposisikan, diresize, dan disesuaikan di editor.'],
                    ['Apakah fitur ini premium?', 'Akses Remove BG dapat mengikuti paket atau kebijakan fitur premium AdaAcara.'],
                ],
            ],
        ];
    }

    public static function find(string $path): ?array
    {
        $path = trim($path, '/');
        $pages = self::pages();

        return $pages[$path] ?? null;
    }
}
