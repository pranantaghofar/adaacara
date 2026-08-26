<?php
    helper('seo');

    $plans = is_array($plans ?? null) ? $plans : [];
    $business = is_array($business ?? null) ? $business : [];
    $brandName = (string) ($business['brand'] ?? 'AdaAcara.com');
    $businessName = (string) ($business['name'] ?? 'PT Shagania Labs Indonesia');
    $businessAddress = (string) ($business['address'] ?? 'Pulogebang, Cakung, Jakarta Timur, DKI Jakarta, Indonesia');
    $businessWhatsapp = (string) ($business['whatsapp'] ?? 'Nomor WhatsApp resmi AdaAcara');
    $businessWhatsappUrl = trim((string) ($business['whatsapp_url'] ?? ''));
    $businessEmail = (string) ($business['email'] ?? 'hello@adaacara.com');

    $formatRupiah = static function (int $amount): string {
        return $amount > 0 ? 'Rp ' . number_format($amount, 0, ',', '.') : 'Gratis';
    };

    $formatPeriod = static function (int $days): string {
        if ($days <= 0) {
            return 'mengikuti ketentuan paket';
        }

        if ($days >= 365 && $days % 365 === 0) {
            $years = (int) ($days / 365);
            return $years . ' tahun';
        }

        if ($days >= 30 && $days % 30 === 0) {
            $months = (int) ($days / 30);
            return $months . ' bulan';
        }

        return $days . ' hari';
    };
    $normalizePlanKey = static function ($value): string {
        return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $value)), '-');
    };
    $isLifetimePlan = static function (array $plan) use ($normalizePlanKey): bool {
        if (((int) ($plan['is_lifetime'] ?? 0)) !== 1) {
            return false;
        }

        $keys = [
            $normalizePlanKey($plan['slug'] ?? ''),
            $normalizePlanKey($plan['name'] ?? ''),
        ];

        return count(array_intersect($keys, ['business', 'busseniss', 'buat-niat-jualan'])) > 0;
    };

    $planTitle = static function (array $plan): string {
        $key = strtolower((string) ($plan['slug'] ?? $plan['name'] ?? ''));

        return match ($key) {
            'basic', 'starter' => 'Buat Acara Sendiri',
            'premium', 'buat-nyoba-jualan', 'buat-coba-jualan' => 'Buat Coba Jualan',
            'business', 'busseniss' => 'Buat Niat Jualan',
            default => (string) ($plan['name'] ?? 'Paket AdaAcara'),
        };
    };

    $planAudience = static function (array $plan): string {
        $key = strtolower((string) ($plan['slug'] ?? $plan['name'] ?? ''));

        return match ($key) {
            'basic', 'starter' => 'Untuk pengguna yang ingin membuat undangan website sendiri.',
            'premium', 'buat-nyoba-jualan', 'buat-coba-jualan' => 'Untuk mulai menjual jasa undangan digital dengan fitur lebih lengkap.',
            'business', 'busseniss' => ((int) ($plan['is_lifetime'] ?? 0)) === 1
                ? 'Untuk seller yang butuh akses aktif selamanya dan operasional lebih stabil.'
                : 'Untuk seller yang butuh akses lebih panjang dan operasional lebih stabil.',
            default => (string) ($plan['description'] ?: 'Untuk kebutuhan pembuatan undangan website digital.'),
        };
    };

    $planBenefits = static function (array $plan): array {
        $key = strtolower((string) ($plan['slug'] ?? $plan['name'] ?? ''));

        return match ($key) {
            'basic', 'starter' => [
                'Publish link undangan aktif tanpa batas selama masa paket.',
                'Akses editor visual untuk ubah teks, foto, warna, musik, dan halaman.',
                'Fitur RSVP, guestbook, gallery, QR code, dan wedding gift.',
            ],
            'premium', 'buat-nyoba-jualan', 'buat-coba-jualan' => [
                'Semua fitur paket personal dengan akses template premium.',
                'Cocok untuk mulai menjual jasa undangan digital kepada customer.',
                'Akses fitur editor lanjutan seperti animasi, efek, dan AI tools.',
            ],
            'business', 'busseniss' => [
                ((int) ($plan['is_lifetime'] ?? 0)) === 1
                    ? 'Semua fitur premium dengan akses aktif selamanya.'
                    : 'Semua fitur premium dengan masa aktif lebih panjang.',
                'Mendukung operasional seller, customer, dan publish link lebih leluasa.',
                'Akses template premium, media library, RSVP, guestbook, dan AI tools.',
            ],
            default => [
                'Akses editor visual AdaAcara sesuai ketentuan paket.',
                'Buat, preview, dan publish undangan menjadi link publik.',
                'Kelola konten undangan dari dashboard pengguna.',
            ],
        };
    };

    $schemaPlans = array_map(static function (array $plan) use ($formatRupiah, $planTitle): array {
        return [
            'name' => $planTitle($plan),
            'price' => $formatRupiah((int) ($plan['price'] ?? 0)),
            'url' => site_url('checkout/' . (string) ($plan['slug'] ?? '')),
        ];
    }, $plans);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->title('Informasi Pembelian Paket AdaAcara untuk Xendit')
        ->description('Informasi resmi AdaAcara berisi penjelasan produk digital, paket harga, cara pembelian, aktivasi, refund, badan usaha, kontak, syarat, dan privasi.')
        ->canonical(site_url('activate-xendit'))
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => 'Informasi Pembelian', 'url' => site_url('activate-xendit')],
        ])
        ->render() ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/public_theme_assets') ?>
    <style>
        body.aa-xendit-page {
            margin: 0;
            background:
                radial-gradient(circle at 12% 8%, rgba(168, 120, 241, .16), transparent 30%),
                radial-gradient(circle at 88% 16%, rgba(251, 191, 36, .15), transparent 28%),
                linear-gradient(180deg, #fff8f5 0%, #fff 38%, #f8fafc 100%);
            color: #30234b;
            font-family: "Plus Jakarta Sans", Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .aa-xendit-shell {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .aa-xendit-hero {
            padding: 64px 0 28px;
        }

        .aa-xendit-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(340px, .92fr);
            gap: 24px;
            align-items: stretch;
        }

        .aa-xendit-panel,
        .aa-xendit-card {
            border: 1px solid rgba(135, 101, 190, .18);
            background: rgba(255, 255, 255, .82);
            box-shadow: 0 24px 70px rgba(76, 55, 112, .12);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .aa-xendit-panel {
            border-radius: 34px;
            padding: clamp(28px, 4vw, 52px);
        }

        .aa-xendit-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            border-radius: 999px;
            background: rgba(143, 101, 223, .12);
            padding: 9px 13px;
            color: #7b4fd3;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .aa-xendit-eyebrow::before {
            content: "";
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #9c74e6;
            box-shadow: 0 0 0 6px rgba(156, 116, 230, .13);
        }

        .aa-xendit-title {
            max-width: 820px;
            margin: 22px 0 0;
            color: #2f2548;
            font-size: clamp(34px, 5vw, 68px);
            line-height: .98;
            letter-spacing: 0;
        }

        .aa-xendit-lead {
            max-width: 720px;
            margin: 20px 0 0;
            color: #6e6382;
            font-size: clamp(16px, 1.8vw, 20px);
            font-weight: 700;
            line-height: 1.75;
        }

        .aa-xendit-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .aa-xendit-btn {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0 20px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 900;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .aa-xendit-btn:hover {
            transform: translateY(-2px);
        }

        .aa-xendit-btn-primary {
            border: 1px solid rgba(143, 101, 223, .5);
            background: linear-gradient(135deg, #a878f1 0%, #8158d8 100%);
            color: #fff;
            box-shadow: 0 18px 40px rgba(129, 88, 216, .24);
        }

        .aa-xendit-btn-secondary {
            border: 1px solid rgba(143, 101, 223, .22);
            background: rgba(255, 255, 255, .9);
            color: #493966;
        }

        .aa-xendit-summary {
            display: grid;
            gap: 14px;
        }

        .aa-xendit-info-card {
            border-radius: 28px;
            padding: 22px;
        }

        .aa-xendit-info-card h2,
        .aa-xendit-card h2,
        .aa-xendit-section h2 {
            margin: 0;
            color: #312447;
            font-size: 24px;
            line-height: 1.2;
        }

        .aa-xendit-info-list {
            display: grid;
            gap: 12px;
            margin-top: 18px;
        }

        .aa-xendit-info-list div {
            border-radius: 18px;
            background: rgba(248, 246, 252, .86);
            padding: 14px 15px;
        }

        .aa-xendit-info-list dt {
            margin: 0 0 5px;
            color: #7d7192;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .11em;
        }

        .aa-xendit-info-list dd {
            margin: 0;
            color: #3f315d;
            font-size: 14px;
            font-weight: 800;
            line-height: 1.5;
        }

        .aa-xendit-info-list a {
            color: inherit;
            text-decoration: none;
        }

        .aa-xendit-section {
            padding: 24px 0;
        }

        .aa-xendit-section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 18px;
        }

        .aa-xendit-section-head p,
        .aa-xendit-card p {
            margin: 8px 0 0;
            color: #716783;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.65;
        }

        .aa-xendit-plan-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .aa-xendit-card {
            border-radius: 28px;
            padding: 22px;
        }

        .aa-xendit-price {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            gap: 8px;
            margin-top: 18px;
        }

        .aa-xendit-price strong {
            color: #211934;
            font-size: 34px;
            line-height: 1;
        }

        .aa-xendit-price span {
            color: #8a7a9f;
            font-size: 13px;
            font-weight: 900;
        }

        .aa-xendit-compare {
            margin: 10px 0 0;
            color: #9a8faa;
            font-size: 13px;
            font-weight: 800;
        }

        .aa-xendit-compare s {
            color: #9a8faa;
        }

        .aa-xendit-benefits,
        .aa-xendit-steps {
            display: grid;
            gap: 10px;
            margin: 18px 0 0;
            padding: 0;
            list-style: none;
        }

        .aa-xendit-benefits li,
        .aa-xendit-steps li {
            position: relative;
            border-radius: 16px;
            background: rgba(248, 246, 252, .78);
            padding: 12px 12px 12px 36px;
            color: #574b6d;
            font-size: 13px;
            font-weight: 800;
            line-height: 1.55;
        }

        .aa-xendit-benefits li::before,
        .aa-xendit-steps li::before {
            content: "";
            position: absolute;
            top: 16px;
            left: 15px;
            width: 9px;
            height: 9px;
            border-radius: 999px;
            background: #9c74e6;
            box-shadow: 0 0 0 5px rgba(156, 116, 230, .12);
        }

        .aa-xendit-card .aa-xendit-btn {
            width: 100%;
            margin-top: 18px;
        }

        .aa-xendit-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .aa-xendit-flow {
            counter-reset: flow;
        }

        .aa-xendit-flow li {
            counter-increment: flow;
        }

        .aa-xendit-flow li::before {
            content: counter(flow);
            top: 11px;
            width: 20px;
            height: 20px;
            display: grid;
            place-items: center;
            background: #fff;
            color: #7b4fd3;
            border: 1px solid rgba(156, 116, 230, .32);
            box-shadow: 0 10px 22px rgba(129, 88, 216, .12);
            font-size: 11px;
            font-weight: 900;
        }

        .aa-xendit-note {
            border: 1px solid rgba(245, 158, 11, .26);
            background: linear-gradient(135deg, rgba(255, 251, 235, .92), rgba(255, 247, 237, .72));
            color: #7c4a03;
        }

        .aa-xendit-footer-card {
            margin: 24px auto 56px;
            border-radius: 34px;
            padding: clamp(24px, 4vw, 42px);
            text-align: center;
        }

        .aa-xendit-footer-card p {
            max-width: 720px;
            margin-left: auto;
            margin-right: auto;
        }

        html[data-aa-public-theme="dark"] body.aa-xendit-page {
            background:
                radial-gradient(circle at 12% 8%, rgba(168, 120, 241, .18), transparent 30%),
                radial-gradient(circle at 88% 16%, rgba(251, 191, 36, .12), transparent 28%),
                linear-gradient(180deg, #100e18 0%, #171326 45%, #0f172a 100%);
            color: #f8fafc;
        }

        html[data-aa-public-theme="dark"] .aa-xendit-panel,
        html[data-aa-public-theme="dark"] .aa-xendit-card {
            border-color: rgba(255, 255, 255, .12);
            background: rgba(28, 24, 43, .72);
            box-shadow: 0 24px 70px rgba(0, 0, 0, .28);
        }

        html[data-aa-public-theme="dark"] .aa-xendit-title,
        html[data-aa-public-theme="dark"] .aa-xendit-info-card h2,
        html[data-aa-public-theme="dark"] .aa-xendit-card h2,
        html[data-aa-public-theme="dark"] .aa-xendit-section h2,
        html[data-aa-public-theme="dark"] .aa-xendit-price strong {
            color: #fff;
        }

        html[data-aa-public-theme="dark"] .aa-xendit-lead,
        html[data-aa-public-theme="dark"] .aa-xendit-section-head p,
        html[data-aa-public-theme="dark"] .aa-xendit-card p {
            color: #c4bad2;
        }

        html[data-aa-public-theme="dark"] .aa-xendit-info-list div,
        html[data-aa-public-theme="dark"] .aa-xendit-benefits li,
        html[data-aa-public-theme="dark"] .aa-xendit-steps li {
            background: rgba(255, 255, 255, .07);
            color: #e2d9ef;
        }

        html[data-aa-public-theme="dark"] .aa-xendit-info-list dt,
        html[data-aa-public-theme="dark"] .aa-xendit-price span,
        html[data-aa-public-theme="dark"] .aa-xendit-compare,
        html[data-aa-public-theme="dark"] .aa-xendit-compare s {
            color: #b8aeca;
        }

        html[data-aa-public-theme="dark"] .aa-xendit-info-list dd,
        html[data-aa-public-theme="dark"] .aa-xendit-info-list a {
            color: #f4eefc;
        }

        html[data-aa-public-theme="dark"] .aa-xendit-btn-secondary {
            border-color: rgba(255, 255, 255, .14);
            background: rgba(255, 255, 255, .08);
            color: #fff;
        }

        html[data-aa-public-theme="dark"] .aa-xendit-note {
            border-color: rgba(251, 191, 36, .24);
            background: rgba(120, 77, 18, .24);
            color: #fde68a;
        }

        @media (max-width: 980px) {
            .aa-xendit-hero-grid,
            .aa-xendit-grid-2 {
                grid-template-columns: 1fr;
            }

            .aa-xendit-plan-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .aa-xendit-shell {
                width: min(100% - 24px, 1180px);
            }

            .aa-xendit-hero {
                padding-top: 36px;
            }

            .aa-xendit-panel,
            .aa-xendit-card {
                border-radius: 24px;
            }

            .aa-xendit-section-head {
                align-items: start;
                flex-direction: column;
            }

            .aa-xendit-plan-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body class="aa-app-ui aa-public-theme-page aa-xendit-page">
    <?= view('components/public_site_header', ['active' => 'plans']) ?>

    <main>
        <section class="aa-xendit-shell aa-xendit-hero">
            <div class="aa-xendit-hero-grid">
                <div class="aa-xendit-panel">
                    <span class="aa-xendit-eyebrow">Informasi Pembelian Resmi</span>
                    <h1 class="aa-xendit-title">AdaAcara adalah platform pembuat undangan website digital.</h1>
                    <p class="aa-xendit-lead">Pengguna dapat memilih template, mengedit desain, menambahkan RSVP, guestbook, musik, galeri, QR code, wedding gift, lalu mem-publish undangan sebagai link publik yang siap dibagikan.</p>
                    <div class="aa-xendit-actions">
                        <a class="aa-xendit-btn aa-xendit-btn-primary" href="<?= site_url('plans') ?>">Lihat Paket Harga</a>
                        <a class="aa-xendit-btn aa-xendit-btn-secondary" href="<?= site_url('templates') ?>">Lihat Template</a>
                    </div>
                </div>

                <aside class="aa-xendit-panel aa-xendit-info-card" aria-label="Informasi bisnis AdaAcara">
                    <h2>Informasi Bisnis</h2>
                    <dl class="aa-xendit-info-list">
                        <div>
                            <dt>Nama brand</dt>
                            <dd><?= esc($brandName) ?></dd>
                        </div>
                        <div>
                            <dt>Nama badan usaha</dt>
                            <dd><?= esc($businessName) ?></dd>
                        </div>
                        <div>
                            <dt>Alamat bisnis</dt>
                            <dd><?= esc($businessAddress) ?></dd>
                        </div>
                        <div>
                            <dt>WhatsApp</dt>
                            <dd>
                                <?php if ($businessWhatsappUrl !== ''): ?>
                                    <a href="<?= esc($businessWhatsappUrl, 'attr') ?>" target="_blank" rel="noopener"><?= esc($businessWhatsapp) ?></a>
                                <?php else: ?>
                                    <?= esc($businessWhatsapp) ?>
                                <?php endif ?>
                            </dd>
                        </div>
                        <div>
                            <dt>Email</dt>
                            <dd><a href="mailto:<?= esc($businessEmail, 'attr') ?>"><?= esc($businessEmail) ?></a></dd>
                        </div>
                    </dl>
                </aside>
            </div>
        </section>

        <section class="aa-xendit-shell aa-xendit-section" id="paket">
            <div class="aa-xendit-section-head">
                <div>
                    <h2>Daftar paket dan harga</h2>
                    <p>Paket di bawah mengikuti data aktif pada halaman harga AdaAcara.</p>
                </div>
                <a class="aa-xendit-btn aa-xendit-btn-secondary" href="<?= site_url('plans') ?>">Buka Halaman Harga</a>
            </div>

            <?php if ($plans === []): ?>
                <article class="aa-xendit-card">
                    <h2>Paket sedang disiapkan</h2>
                    <p>Daftar paket belum tersedia. Silakan hubungi AdaAcara melalui email resmi untuk informasi pembelian.</p>
                </article>
            <?php else: ?>
                <div class="aa-xendit-plan-grid">
                    <?php foreach ($plans as $plan): ?>
                        <?php
                            $price = (int) ($plan['price'] ?? 0);
                            $compareAtPrice = (int) ($plan['compare_at_price'] ?? 0);
                            $hasDiscount = $compareAtPrice > $price && $price > 0;
                            $slug = (string) ($plan['slug'] ?? '');
                        ?>
                        <article class="aa-xendit-card">
                            <h2><?= esc($planTitle($plan)) ?></h2>
                            <p><?= esc($planAudience($plan)) ?></p>
                            <?php if ($hasDiscount): ?>
                                <p class="aa-xendit-compare">Harga sebelum diskon <s><?= esc($formatRupiah($compareAtPrice)) ?></s></p>
                            <?php endif ?>
                            <div class="aa-xendit-price">
                                <strong><?= esc($formatRupiah($price)) ?></strong>
                                <span>/ <?= esc($isLifetimePlan($plan) ? 'selamanya' : $formatPeriod((int) ($plan['active_days'] ?? 0))) ?></span>
                            </div>
                            <ul class="aa-xendit-benefits">
                                <?php foreach ($planBenefits($plan) as $benefit): ?>
                                    <li><?= esc($benefit) ?></li>
                                <?php endforeach ?>
                            </ul>
                            <?php if ($slug !== ''): ?>
                                <a class="aa-xendit-btn aa-xendit-btn-primary" href="<?= site_url('checkout/' . $slug) ?>">Beli Paket</a>
                            <?php endif ?>
                        </article>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
        </section>

        <section class="aa-xendit-shell aa-xendit-section">
            <div class="aa-xendit-grid-2">
                <article class="aa-xendit-card">
                    <h2>Cara kerja pembelian</h2>
                    <ol class="aa-xendit-steps aa-xendit-flow">
                        <li>Pilih paket di halaman ini atau di halaman harga AdaAcara.</li>
                        <li>Login atau daftar akun agar paket bisa terhubung ke dashboard pengguna.</li>
                        <li>Lanjutkan checkout dan pilih metode pembayaran yang tersedia.</li>
                        <li>Setelah pembayaran berhasil, sistem menerima notifikasi payment gateway.</li>
                        <li>Status order berubah menjadi paid dan akses paket aktif otomatis.</li>
                    </ol>
                </article>

                <article class="aa-xendit-card">
                    <h2>Cara aktivasi produk digital</h2>
                    <ul class="aa-xendit-benefits">
                        <li>Produk yang dibeli adalah akses digital untuk editor, template, dan publish link AdaAcara.</li>
                        <li>Aktivasi dilakukan otomatis setelah pembayaran berhasil dan notifikasi payment gateway terverifikasi.</li>
                        <li>Jika notifikasi pembayaran terlambat, admin dapat membantu pengecekan manual berdasarkan invoice dan email akun.</li>
                        <li>Setelah aktif, pengguna dapat membuka dashboard, membuat undangan, mengedit template, dan membagikan link publik.</li>
                    </ul>
                </article>
            </div>
        </section>

        <section class="aa-xendit-shell aa-xendit-section">
            <div class="aa-xendit-grid-2">
                <article class="aa-xendit-card">
                    <h2>Kebijakan refund</h2>
                    <ul class="aa-xendit-benefits">
                        <li>Refund dapat diajukan jika terjadi pembayaran ganda, kesalahan sistem, atau paket belum aktif setelah pembayaran berhasil.</li>
                        <li>Refund tidak berlaku jika paket sudah aktif, fitur sudah digunakan, atau undangan sudah dipublish oleh pengguna.</li>
                        <li>Pengajuan refund perlu menyertakan email akun, nomor invoice, nominal pembayaran, dan bukti transaksi.</li>
                        <li>Jika disetujui, refund diproses mengikuti kanal pembayaran dan ketentuan payment gateway yang digunakan.</li>
                    </ul>
                </article>

                <article class="aa-xendit-card">
                    <h2>Syarat dan kebijakan privasi</h2>
                    <p>Pengguna wajib membaca dan menyetujui ketentuan penggunaan layanan, kebijakan privasi, serta penggunaan cookie yang berlaku di AdaAcara.</p>
                    <div class="aa-xendit-actions">
                        <a class="aa-xendit-btn aa-xendit-btn-secondary" href="<?= site_url('terms') ?>">Syarat & Ketentuan</a>
                        <a class="aa-xendit-btn aa-xendit-btn-secondary" href="<?= site_url('privacy') ?>">Kebijakan Privasi</a>
                        <a class="aa-xendit-btn aa-xendit-btn-secondary" href="<?= site_url('cookies') ?>">Kebijakan Cookie</a>
                    </div>
                </article>
            </div>
        </section>

        <section class="aa-xendit-shell aa-xendit-section">
            <article class="aa-xendit-card aa-xendit-note">
                <h2>Catatan checkout</h2>
                <p>Tombol Beli Paket mengarah ke halaman checkout AdaAcara. Jika pengguna belum login, sistem akan meminta login terlebih dahulu agar pembayaran dan aktivasi paket tersimpan pada akun yang benar.</p>
            </article>
        </section>

        <section class="aa-xendit-shell">
            <article class="aa-xendit-panel aa-xendit-footer-card">
                <span class="aa-xendit-eyebrow">Produk Digital AdaAcara</span>
                <h2 class="aa-xendit-title" style="font-size: clamp(30px, 4vw, 52px);">Siap membeli paket AdaAcara?</h2>
                <p class="aa-xendit-lead">Mulai dari template siap edit atau canvas kosong, lalu publish undangan sebagai link website yang dapat dibagikan ke tamu.</p>
                <div class="aa-xendit-actions" style="justify-content: center;">
                    <a class="aa-xendit-btn aa-xendit-btn-primary" href="<?= site_url('plans') ?>">Pilih Paket</a>
                    <a class="aa-xendit-btn aa-xendit-btn-secondary" href="<?= site_url('templates') ?>">Lihat Template</a>
                </div>
            </article>
        </section>
    </main>

    <?= view('components/site_footer') ?>

    <script type="application/ld+json">
        <?= json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => 'AdaAcara',
            'url' => site_url('activate-xendit'),
            'applicationCategory' => 'DesignApplication',
            'operatingSystem' => 'Web',
            'description' => 'AdaAcara adalah platform pembuat undangan website digital dengan editor visual, template, RSVP, guestbook, musik, galeri, QR code, wedding gift, dan publish link.',
            'provider' => [
                '@type' => 'Organization',
                'name' => $businessName,
                'brand' => $brandName,
                'email' => $businessEmail,
                'address' => $businessAddress,
            ],
            'offers' => array_map(static function (array $plan) use ($planTitle): array {
                return [
                    '@type' => 'Offer',
                    'name' => $planTitle($plan),
                    'price' => (int) ($plan['price'] ?? 0),
                    'priceCurrency' => 'IDR',
                    'url' => site_url('checkout/' . (string) ($plan['slug'] ?? '')),
                    'availability' => 'https://schema.org/InStock',
                ];
            }, $plans),
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Paket AdaAcara',
                'itemListElement' => $schemaPlans,
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
    </script>
</body>
</html>
