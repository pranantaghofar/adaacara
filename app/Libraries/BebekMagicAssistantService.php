<?php

namespace App\Libraries;

use RuntimeException;

class BebekMagicAssistantService
{
    public function reply(string $message, array $context = []): string
    {
        $message = $this->sanitizeMessage($message);
        if ($message === '') {
            throw new RuntimeException('Tulis pertanyaan dulu ya Kak.');
        }

        $provider = strtolower(trim((string) env('BEBEK_MAGIC_PROVIDER', 'local')));
        if ($provider === 'openai-compatible') {
            return $this->remoteReply($message, $context);
        }

        return $this->localReply($message);
    }

    public function sanitizeMessage(string $message): string
    {
        $message = trim(strip_tags($message));
        $message = preg_replace('/\s+/u', ' ', $message) ?? $message;

        return mb_substr($message, 0, 800);
    }

    private function remoteReply(string $message, array $context): string
    {
        $endpoint = trim((string) env('BEBEK_MAGIC_ENDPOINT', ''));
        $apiKey = trim((string) env('BEBEK_MAGIC_API_KEY', ''));
        $model = trim((string) env('BEBEK_MAGIC_MODEL', ''));

        if ($endpoint === '' || $apiKey === '' || $model === '') {
            return $this->localReply($message);
        }

        $payload = [
            'model' => $model,
            'temperature' => 0.35,
            'max_tokens' => 450,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemInstruction(),
                ],
                [
                    'role' => 'user',
                    'content' => $message,
                ],
            ],
        ];

        $client = service('curlrequest', [
            'timeout' => (int) env('BEBEK_MAGIC_TIMEOUT', 20),
            'http_errors' => false,
        ]);

        $response = $client->post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json; charset=utf-8',
                'Accept' => 'application/json',
            ],
            'body' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        if ($status < 200 || $status >= 300) {
            log_message('error', 'Bebek Magic provider gagal: status={status}, body={body}', [
                'status' => (string) $status,
                'body' => mb_substr($body, 0, 800),
            ]);

            throw new RuntimeException('Bebek Magic sedang sibuk sebentar. Coba lagi ya Kak.');
        }

        $json = json_decode($body, true);
        $text = trim((string) ($json['choices'][0]['message']['content'] ?? $json['choices'][0]['text'] ?? ''));
        if ($text === '') {
            throw new RuntimeException('Bebek Magic belum bisa menjawab. Coba ulang dengan pertanyaan lain ya Kak.');
        }

        return mb_substr($text, 0, 1600);
    }

    private function localReply(string $message): string
    {
        $lower = mb_strtolower($message);

        if ($this->containsAny($lower, ['magic layer', 'scan teks', 'ocr', 'teks di gambar', 'gambar ada teks'])) {
            return "Halo Kak! Bebek Magic bantu ya.\n\n1. Buka halaman Editor.\n2. Masuk ke panel Magic Layer di kiri.\n3. Upload gambar maksimal sesuai batas yang tampil.\n4. Tunggu proses selesai, lalu cek ulang teks dan posisi hasilnya.\n\nCatatan: hasil AI bisa perlu dirapikan sedikit sebelum disimpan atau dipublish.";
        }

        if ($this->containsAny($lower, ['remove bg', 'hapus background', 'background hilang'])) {
            return "Halo Kak! Untuk hapus background:\n\n1. Pilih gambar di canvas editor.\n2. Klik Remove BG.\n3. Tunggu proses selesai.\n4. Kalau hasil belum rapi, coba gambar dengan objek utama yang lebih jelas.\n\nFitur ini termasuk fitur premium jika paket belum aktif.";
        }

        if ($this->containsAny($lower, ['publish', 'terbit', 'bagikan', 'url', 'link undangan'])) {
            return "Untuk publish undangan:\n\n1. Pastikan desain sudah disimpan.\n2. Klik Preview untuk cek tampilan.\n3. Klik Publish.\n4. Isi slug URL yang rapi.\n5. Bagikan link public ke tamu.\n\nKalau tombol Publish terkunci, cek paket aktif di halaman Paket.";
        }

        if ($this->containsAny($lower, ['template', 'mulai desain', 'buat undangan', 'mulai dari mana'])) {
            return "Mulai paling mudah begini Kak:\n\n1. Klik menu Template.\n2. Pilih desain yang paling dekat dengan acara Kakak.\n3. Isi judul acara, tanggal, dan slug.\n4. Masuk Editor untuk ganti teks, foto, warna, dan font.\n5. Preview lalu publish saat sudah siap.";
        }

        if ($this->containsAny($lower, ['dashboard', 'riwayat', 'desain saya', 'undangan saya'])) {
            return "Di Dashboard, Kakak bisa melihat daftar undangan/desain yang pernah dibuat, membuka editor lagi, cek guestbook, dan mengatur publish. Kalau belum login, masuk dulu lewat tombol Login.";
        }

        if ($this->containsAny($lower, ['harga', 'paket', 'premium', 'member', 'creator', 'seller'])) {
            return "Untuk paket dan akses fitur, buka halaman Paket ya Kak. Di sana Kakak bisa membandingkan akses member, seller, dan creator. Bebek Magic tidak bisa melihat status pembayaran pribadi, jadi untuk kendala pembayaran sebaiknya hubungi admin.";
        }

        return "Halo Kak! Aku Bebek Magic, asisten AdaAcara.\n\nAku bisa bantu jelaskan cara mulai desain, memilih template, memakai Editor, Magic Layer, Remove BG, Dashboard, dan Publish.\n\nCoba tanya misalnya: “cara pakai Magic Layer” atau “cara publish undangan”.";
    }

    private function containsAny(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function systemInstruction(): string
    {
        return <<<'PROMPT'
Anda adalah Bebek Magic, AI assistant resmi AdaAcara.
Tugas Anda hanya membantu navigasi, tutorial fitur, dan troubleshooting ringan di website AdaAcara.

Konteks produk:
- Home: pengenalan fitur, template, paket, creator/seller, dan tombol mulai desain.
- Dashboard: daftar desain/undangan, guestbook, share WhatsApp, status publish.
- Editor: edit canvas visual, upload media, ganti teks/foto/font/warna, preview, save draft, publish.
- Magic Layer: upload/scan gambar agar teks dapat dibuat editable.
- Remove BG: hapus background gambar.
- ACARA AI: membantu membuat atau merombak desain di editor.

Aturan aman:
- Jangan mengaku bisa melihat status pembayaran, kuota, data pribadi, atau isi akun user.
- Jangan meminta API key, password, token, atau data sensitif.
- Jangan memberikan instruksi server, database, atau file rahasia.
- Jika tidak yakin, jawab jujur dan arahkan user menghubungi admin.
- Jawab singkat, ramah, dan praktis dalam Bahasa Indonesia.
PROMPT;
    }
}
