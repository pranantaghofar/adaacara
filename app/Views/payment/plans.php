<!doctype html>
<?php
    helper('seo');

    $isLoggedIn = (bool) (session()->get('isLoggedIn') ?? session()->get('userId'));
    $plans = is_array($plans ?? null) ? $plans : [];
    $activeSubscription = $activeSubscription ?? null;
    $latestOrdersByPlan = is_array($latestOrdersByPlan ?? null) ? $latestOrdersByPlan : [];
    $creatorStatus = $creatorStatus ?? ['status' => 'none'];
    $creatorFlowStatus = (string) ($creatorStatus['status'] ?? 'none');
    $creatorBlocksMembership = in_array($creatorFlowStatus, ['pending', 'active'], true);

    $normalizePlanKey = static function ($value): string {
        return trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $value)), '-');
    };

    $planRank = static function (string $planKey): int {
        return match (strtolower(trim($planKey))) {
            'basic', 'starter', 'buat-pakai-sendiri', 'buat-acara-sendiri' => 1,
            'premium', 'plus', 'buat-nyoba-jualan', 'buat-coba-jualan' => 2,
            'business', 'ultimate', 'busseniss', 'buat-niat-jualan' => 3,
            default => 0,
        };
    };

    $activePlanKey = strtolower((string) ($activeSubscription['plan_slug'] ?? $activeSubscription['plan_name'] ?? ''));
    $activePlanRank = $planRank($activePlanKey);

    $formatActiveMonths = static function ($days): string {
        $activeDays = max(1, (int) $days);
        $months = max(1, (int) round($activeDays / 30));

        return $months . ' bulan';
    };

    $isLifetimePlan = static function (array $plan) use ($normalizePlanKey): bool {
        if (((int) ($plan['is_lifetime'] ?? 0)) !== 1) {
            return false;
        }

        $keys = [
            $normalizePlanKey($plan['slug'] ?? ''),
            $normalizePlanKey($plan['name'] ?? ''),
        ];

        return count(array_intersect($keys, ['business', 'ultimate', 'busseniss', 'buat-niat-jualan'])) > 0;
    };

    $checkSvg = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 6 9 17l-5-5" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    $crownSvg = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m3 8 4.5 4L12 5l4.5 7L21 8l-2 10H5L3 8Z" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 21h14" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>';
    $infoSvg = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 17v-6" stroke="currentColor" stroke-width="2.3" stroke-linecap="round"/><path d="M12 8h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>';
    $minusSvg = '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 12h10" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>';

    $planConfigs = [
        1 => [
            'name' => 'Starter',
            'tagline' => 'Untuk kebutuhan personal',
            'icon' => 'user',
            'tone' => 'starter',
            'button' => 'Mulai dengan Starter',
            'description' => 'Cocok untuk membuat acara pribadi atau mencoba semua kemampuan AdaAcara.',
            'featureIntro' => 'Yang kamu dapatkan:',
            'features' => [
                'AdaAcara Studio (Editor Drag & Drop)',
                'Template & Asset Premium',
                'Undangan Digital Interaktif',
                'RSVP, Guestbook & Gallery',
                'Animasi & Text Effects',
                'Remove Background',
                'ACARA AI & Magic Layer AI',
                'Unlimited Publish Link',
            ],
        ],
        2 => [
            'name' => 'Plus',
            'tagline' => 'Untuk freelancer & usaha',
            'icon' => 'crown',
            'tone' => 'plus',
            'button' => 'Pilih Plus',
            'description' => 'Untuk kamu yang aktif membuat acara atau mulai menawarkan layanan ke customer.',
            'featureIntro' => 'Semua fitur di Starter, plus:',
            'features' => [
                'Digital Photobooth',
                'Frame Photobooth dari Studio',
                'QR Photobooth & Memories Gallery',
                'Download foto dengan kode akses',
                'Sistem Print untuk operator di venue',
                'Unlimited Project & Publish',
                'Bisa digunakan untuk project customer',
                'Akses update fitur selama membership',
            ],
        ],
        3 => [
            'name' => 'Ultimate',
            'tagline' => 'Untuk penggunaan jangka panjang',
            'icon' => 'diamond',
            'tone' => 'ultimate',
            'button' => 'Gunakan untuk Bisnis',
            'description' => 'Untuk vendor, agency, dan pelaku usaha yang membutuhkan akses tanpa batas selamanya.',
            'featureIntro' => 'Semua fitur di Plus, plus:',
            'features' => [
                'Akses membership selamanya',
                'Unlimited project untuk semua client',
                'Seluruh fitur premium Studio',
                'ACARA AI, Magic Layer AI & Remove BG',
                'Premium template & asset tanpa batas',
                'Fitur bisnis yang tersedia untuk Ultimate',
                'Prioritas dukungan & update fitur baru',
            ],
        ],
    ];

    $makePlanCard = static function (array $plan) use (
        $normalizePlanKey,
        $planRank,
        $isLifetimePlan,
        $formatActiveMonths,
        $activeSubscription,
        $activePlanRank,
        $creatorBlocksMembership,
        $latestOrdersByPlan,
        $planConfigs
    ): ?array {
        $productType = strtolower(trim((string) ($plan['product_type'] ?? 'membership'))) ?: 'membership';
        if (! in_array($productType, ['membership', 'creator'], true)) {
            return null;
        }

        $planKey = $normalizePlanKey($plan['slug'] ?? $plan['name'] ?? '');
        if ($planKey === 'creator') {
            return null;
        }

        $rank = $planRank($planKey);
        $config = $planConfigs[$rank] ?? [
            'name' => (string) ($plan['name'] ?? 'Paket'),
            'tagline' => 'Untuk kebutuhan AdaAcara',
            'icon' => 'user',
            'tone' => 'starter',
            'button' => 'Pilih Paket',
            'description' => (string) ($plan['description'] ?: 'Paket untuk membuat pengalaman digital acara.'),
            'featureIntro' => 'Fitur paket:',
            'features' => ['Editor visual', 'Publish link', 'Fitur mengikuti konfigurasi paket aktif'],
        ];

        $isLifetime = $isLifetimePlan($plan);
        $periodLabel = $isLifetime ? 'selamanya' : $formatActiveMonths($plan['active_days'] ?? 30);
        $currentPrice = (int) ($plan['price'] ?? 0);
        $compareAtPrice = (int) ($plan['compare_at_price'] ?? 0);
        $hasDiscount = $compareAtPrice > $currentPrice && $currentPrice > 0;
        $discountPercent = $hasDiscount ? max(1, (int) round((($compareAtPrice - $currentPrice) / $compareAtPrice) * 100)) : 0;
        $planId = (int) ($plan['id'] ?? 0);
        $activePlanId = (int) ($activeSubscription['plan_id'] ?? 0);
        $latestOrder = $latestOrdersByPlan[$planId] ?? null;
        $latestOrderStatus = (string) ($latestOrder['status'] ?? '');
        $isPopular = $rank === 2 || in_array($planKey, ['premium', 'plus', 'buat-nyoba-jualan', 'buat-coba-jualan'], true);
        $actionLabel = (string) ($config['button'] ?? 'Pilih Paket');
        $actionHref = site_url('checkout/' . (string) ($plan['slug'] ?? ''));
        $actionDisabled = false;
        $actionState = 'normal';

        if ($activePlanId === $planId) {
            $actionLabel = 'Paket Aktif';
            $actionHref = '#';
            $actionDisabled = true;
            $actionState = 'disabled';
        } elseif ($creatorBlocksMembership) {
            $actionLabel = 'Tidak tersedia untuk Creator';
            $actionHref = '#';
            $actionDisabled = true;
            $actionState = 'disabled';
        } elseif ($activePlanRank > 0 && $rank > 0 && $rank < $activePlanRank) {
            $actionLabel = 'Downgrade tidak tersedia';
            $actionHref = '#';
            $actionDisabled = true;
            $actionState = 'disabled';
        } elseif ($latestOrder !== null && in_array($latestOrderStatus, ['pending', 'pending_payment', 'rejected'], true)) {
            $actionLabel = $latestOrderStatus === 'rejected' ? 'Upload Ulang Bukti' : 'Perlu Pembayaran';
            $actionHref = site_url('orders/' . (int) ($latestOrder['id'] ?? 0));
            $actionState = 'attention';
        } elseif ($latestOrder !== null && $latestOrderStatus === 'waiting_approval') {
            $actionLabel = 'Sedang Diverifikasi';
            $actionHref = '#';
            $actionDisabled = true;
            $actionState = 'disabled';
        } elseif ($activePlanRank > 0 && $rank > $activePlanRank) {
            $actionLabel = 'Upgrade';
        }

        return [
            'id' => $planId,
            'rank' => $rank,
            'key' => $planKey,
            'name' => $config['name'],
            'tagline' => $config['tagline'],
            'icon' => $config['icon'],
            'tone' => $config['tone'],
            'description' => $config['description'],
            'featureIntro' => $config['featureIntro'],
            'features' => $config['features'],
            'price' => $currentPrice,
            'compareAtPrice' => $compareAtPrice,
            'hasDiscount' => $hasDiscount,
            'discountPercent' => $discountPercent,
            'period' => $periodLabel,
            'isLifetime' => $isLifetime,
            'isPopular' => $isPopular,
            'actionLabel' => $actionLabel,
            'actionHref' => $actionHref,
            'actionDisabled' => $actionDisabled,
            'actionState' => $actionState,
            'raw' => $plan,
        ];
    };

    $displayPlans = [];
    $productPlans = [];
    $productPlanConfigs = [
        'business_profile' => [
            'title' => 'Business Profile',
            'tagline' => 'Website usaha sekali beli',
            'period' => 'sekali bayar',
            'button' => 'Beli Business Profile',
            'features' => ['1 website Business Profile aktif terus', 'Edit teks, foto, layanan, dan harga kapan saja', 'Portfolio, testimonial, maps, WhatsApp, social media', 'Link public siap dibagikan ke calon customer'],
        ],
        'photobooth_standalone' => [
            'title' => 'Digital Photobooth',
            'tagline' => 'Photobooth saja tanpa undangan',
            'period' => '1 tahun',
            'button' => 'Beli Photobooth',
            'features' => ['Frame Photobooth', 'QR dan Memories Gallery', 'Download foto dengan kode akses', 'Tidak membuka paket undangan digital'],
        ],
        'photographer_gallery' => [
            'title' => 'Galeri Klien Fotografer',
            'tagline' => 'Private gallery untuk klien',
            'period' => 'sekali bayar',
            'button' => 'Beli Galeri Klien',
            'features' => ['Dashboard upload dan album foto klien', 'PIN/private gallery, favorit, revisi, dan pilihan cetak', 'Halaman keluarga untuk foto yang boleh dibagikan', 'Aktif terus untuk workflow fotografer'],
        ],
    ];
    foreach (($plans ?? []) as $plan) {
        $productType = strtolower(trim((string) ($plan['product_type'] ?? 'membership'))) ?: 'membership';
        if (isset($productPlanConfigs[$productType])) {
            $config = $productPlanConfigs[$productType];
            $planId = (int) ($plan['id'] ?? 0);
            $latestOrder = $latestOrdersByPlan[$planId] ?? null;
            $latestOrderStatus = (string) ($latestOrder['status'] ?? '');
            $actionLabel = (string) $config['button'];
            $actionHref = site_url('checkout/' . (string) ($plan['slug'] ?? ''));
            $actionDisabled = false;
            $actionState = 'normal';

            if ($latestOrder !== null && in_array($latestOrderStatus, ['pending', 'pending_payment', 'rejected'], true)) {
                $actionLabel = $latestOrderStatus === 'rejected' ? 'Upload Ulang Bukti' : 'Perlu Pembayaran';
                $actionHref = site_url('orders/' . (int) ($latestOrder['id'] ?? 0));
                $actionState = 'attention';
            } elseif ($latestOrder !== null && $latestOrderStatus === 'waiting_approval') {
                $actionLabel = 'Sedang Diverifikasi';
                $actionHref = '#';
                $actionDisabled = true;
                $actionState = 'disabled';
            }

            $productPlans[] = [
                'id' => $planId,
                'type' => $productType,
                'title' => $config['title'],
                'tagline' => $config['tagline'],
                'period' => $config['period'],
                'button' => $actionLabel,
                'href' => $actionHref,
                'disabled' => $actionDisabled,
                'state' => $actionState,
                'price' => (int) ($plan['price'] ?? 0),
                'description' => (string) ($plan['description'] ?: $config['tagline']),
                'features' => $config['features'],
            ];
            continue;
        }

        $card = $makePlanCard((array) $plan);
        if ($card !== null) {
            $displayPlans[] = $card;
        }
    }
    usort($displayPlans, static fn (array $a, array $b): int => (($a['rank'] ?: 99) <=> ($b['rank'] ?: 99)) ?: ((int) $a['price'] <=> (int) $b['price']));
    usort($productPlans, static fn (array $a, array $b): int => (int) $a['price'] <=> (int) $b['price']);

    $fallbackStartUrl = site_url('register');
    foreach ($displayPlans as $card) {
        if (! $card['actionDisabled']) {
            $fallbackStartUrl = (string) $card['actionHref'];
            break;
        }
    }

    $comparisonRows = [
        ['AdaAcara Studio (Editor)', [1 => 'check', 2 => 'check', 3 => 'check']],
        ['Template & Asset Premium', [1 => 'check', 2 => 'check', 3 => 'check']],
        ['Undangan Digital Interaktif', [1 => 'check', 2 => 'check', 3 => 'check']],
        ['RSVP, Guestbook & Gallery', [1 => 'check', 2 => 'check', 3 => 'check']],
        ['Digital Photobooth', [1 => 'minus', 2 => 'check', 3 => 'check']],
        ['QR Photobooth & Memories', [1 => 'minus', 2 => 'check', 3 => 'check']],
        ['Sistem Print untuk Operator', [1 => 'minus', 2 => 'check', 3 => 'check']],
        ['Project untuk Customer', [1 => 'limited', 2 => 'check', 3 => 'check']],
        ['Akses Membership', [1 => '1 Bulan', 2 => '12 Bulan', 3 => 'Selamanya']],
        ['Harga', [1 => null, 2 => null, 3 => null]],
    ];

    $detailTabs = [
        'studio' => [
            'label' => 'Studio & Desain',
            'title' => 'AdaAcara Studio',
            'body' => 'Editor visual drag & drop untuk membuat desain acara tanpa coding dan tanpa software berat.',
            'items' => ['Canvas multi halaman', 'Text, image, shape, musik, gallery', 'Animasi dan text effects', 'Preview lalu publish sebagai link acara'],
        ],
        'invitation' => [
            'label' => 'Undangan Digital',
            'title' => 'Undangan Digital',
            'body' => 'Buat website undangan interaktif yang bisa dibagikan ke tamu melalui URL.',
            'items' => ['RSVP dan guestbook', 'Wedding gift dan maps', 'Countdown dan galeri', 'Link public untuk tamu'],
        ],
        'photobooth' => [
            'label' => 'Digital Photobooth',
            'title' => 'Digital Photobooth di AdaAcara',
            'body' => 'Ubah setiap momen acara jadi kenangan seru. Tamu foto dari HP, pilih frame, kirim, lalu dapat kode akses untuk download atau cetak di venue.',
            'items' => ['Buat frame photobooth bebas dengan Studio', 'QR Code unik untuk setiap acara', 'Memories Gallery tersimpan otomatis', 'Download foto dengan kode akses unik', 'Sistem Print untuk operator'],
        ],
        'ai' => [
            'label' => 'AI Tools',
            'title' => 'AI untuk Studio',
            'body' => 'Percepat produksi desain dengan tools AI yang membantu membaca referensi dan mengolah asset.',
            'items' => ['ACARA AI', 'Magic Layer AI', 'Remove Background', 'Workflow desain lebih cepat'],
        ],
        'business' => [
            'label' => 'Untuk Bisnis',
            'title' => 'Untuk seller, vendor, dan agency',
            'body' => 'Paket Plus dan Ultimate mendukung pekerjaan untuk customer dengan publish project yang lebih leluasa.',
            'items' => ['Bisa dipakai untuk project customer', 'Unlimited project dan publish sesuai paket', 'Cocok untuk jasa undangan dan photobooth digital', 'Dukungan update fitur rutin'],
        ],
    ];

    $plansFaqs = [
        ['Apa perbedaan Starter, Plus, dan Ultimate?', 'Starter cocok untuk kebutuhan personal. Plus cocok untuk freelancer atau usaha yang membuat banyak project. Ultimate cocok untuk penggunaan jangka panjang dengan akses selamanya.'],
        ['Apakah harga sudah termasuk photobooth?', 'Photobooth Digital tersedia mulai paket Plus dan Ultimate. Starter tetap bisa digunakan untuk membuat undangan digital dan fitur acara utama.'],
        ['Apakah bisa untuk jual ke customer?', 'Ya. Paket Plus dan Ultimate bisa digunakan untuk membuat acara klien kamu dan membagikan link undangan tanpa batas. Kamu bebas menawarkan layananmu sendiri.'],
        ['Apakah membership akan otomatis diperpanjang?', 'Tidak otomatis kecuali metode pembayaran dan aturan renewal diaktifkan kemudian. Saat ini mengikuti flow checkout dan order yang tersedia.'],
        ['Bagaimana cara pembayaran?', 'Klik paket yang kamu pilih, lanjut ke checkout, lalu gunakan metode pembayaran yang tersedia di akun AdaAcara.'],
        ['Apakah ada komisi untuk Creator?', 'Creator template memiliki alur terpisah. Jika ingin menjadi creator, gunakan menu Creator dan ikuti proses review admin.'],
    ];
?>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo()
        ->title('Paket Harga - AdaAcara')
        ->description('Pilih paket AdaAcara untuk membuat Undangan Digital, Photobooth Digital, dan pengalaman acara lain melalui AdaAcara Studio.')
        ->canonical(site_url('plans'))
        ->image('https://adaacara.com/assets/img/og-default.png')
        ->breadcrumb([
            ['name' => 'Home', 'url' => site_url('/')],
            ['name' => 'Paket Harga', 'url' => site_url('plans')],
        ])
        ->faq($plansFaqs)
        ->render() ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <?= view('components/public_theme_assets') ?>
    <style>
    :root {
        --aa-plan-ink: #15132f;
        --aa-plan-muted: #625f78;
        --aa-plan-purple: #6f2dbd;
        --aa-plan-purple-soft: #f4edff;
        --aa-plan-gold: #d99a13;
        --aa-plan-gold-soft: #fff3d6;
        --aa-plan-line: rgba(113, 75, 130, .18);
        --aa-plan-card: rgba(255, 255, 255, .88);
    }

    .aa-plans-page {
        min-height: 100vh;
        background:
            radial-gradient(circle at 10% 8%, rgba(143, 101, 223, .14), transparent 26rem),
            radial-gradient(circle at 90% 8%, rgba(251, 191, 36, .16), transparent 24rem),
            linear-gradient(180deg, #fffaf2 0%, #fff 42%, #fffaf2 100%);
        color: var(--aa-plan-ink);
        font-family: "Plus Jakarta Sans", "Manrope", ui-sans-serif, system-ui, sans-serif;
    }

    .aa-plans-main {
        width: min(100% - 32px, 1540px);
        margin: 0 auto;
        padding: clamp(34px, 5vw, 76px) 0 122px;
    }

    .aa-plans-hero {
        position: relative;
        overflow: hidden;
        text-align: center;
        padding: 8px 0 28px;
    }

    .aa-plans-hero::before,
    .aa-plans-hero::after {
        content: "";
        position: absolute;
        top: 50px;
        width: 260px;
        height: 90px;
        border-top: 1px solid rgba(217, 154, 19, .34);
        border-radius: 50%;
        pointer-events: none;
    }

    .aa-plans-hero::before {
        left: -80px;
        transform: rotate(11deg);
    }

    .aa-plans-hero::after {
        right: -80px;
        transform: rotate(-11deg);
    }

    .aa-plans-title {
        margin: 0;
        color: #11102d;
        font-size: clamp(34px, 4.6vw, 58px);
        font-weight: 950;
        letter-spacing: -.045em;
        line-height: 1.06;
    }

    .aa-plans-title span {
        background: linear-gradient(100deg, #d99a13, #8b4cc2 56%, #3b286f);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .aa-plans-subtitle {
        max-width: 760px;
        margin: 18px auto 0;
        color: var(--aa-plan-muted);
        font-size: clamp(15px, 1.8vw, 19px);
        font-weight: 650;
        line-height: 1.7;
    }

    .aa-plans-alert {
        margin: 0 0 18px;
        border: 1px solid rgba(225, 29, 72, .2);
        border-radius: 18px;
        background: rgba(255, 241, 242, .92);
        color: #be123c;
        padding: 14px 16px;
        font-size: 14px;
        font-weight: 800;
    }

    .aa-plans-alert.is-info {
        border-color: rgba(124, 58, 237, .22);
        background: rgba(245, 243, 255, .94);
        color: #5b21b6;
    }

    .aa-pricing-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 22px;
        align-items: stretch;
        margin-bottom: 42px;
    }

    .aa-product-plans {
        margin: 8px 0 46px;
    }

    .aa-product-plans-head {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .aa-product-plans-title {
        margin: 0;
        color: #15132f;
        font-size: clamp(24px, 2.8vw, 34px);
        font-weight: 950;
        letter-spacing: 0;
    }

    .aa-product-plans-subtitle {
        margin: 6px 0 0;
        color: var(--aa-plan-muted);
        font-size: 14px;
        font-weight: 750;
        line-height: 1.6;
    }

    .aa-pricing-card {
        position: relative;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--aa-plan-line);
        border-radius: 16px;
        background: var(--aa-plan-card);
        box-shadow: 0 22px 55px rgba(37, 28, 63, .08);
        padding: clamp(22px, 2.8vw, 36px);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .aa-pricing-card:hover {
        transform: translateY(-4px);
        border-color: rgba(111, 45, 189, .34);
        box-shadow: 0 28px 72px rgba(37, 28, 63, .13);
    }

    .aa-pricing-card.is-plus {
        border-color: rgba(196, 75, 116, .44);
        background:
            radial-gradient(circle at 50% -12%, rgba(143, 101, 223, .14), transparent 20rem),
            rgba(255, 255, 255, .9);
    }

    .aa-pricing-popular {
        position: absolute;
        top: -18px;
        left: 50%;
        transform: translateX(-50%);
        display: inline-flex;
        min-height: 36px;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: linear-gradient(135deg, #b77412, #f0b63b);
        color: #fff;
        box-shadow: 0 16px 32px rgba(217, 154, 19, .22);
        font-size: 12px;
        font-weight: 950;
        letter-spacing: .08em;
        padding: 0 20px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .aa-pricing-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .aa-pricing-name {
        margin: 0;
        color: #4c267e;
        font-size: clamp(25px, 2.4vw, 34px);
        font-weight: 950;
        letter-spacing: -.035em;
    }

    .aa-pricing-tagline {
        margin: 6px 0 0;
        color: #201a35;
        font-size: 14px;
        font-weight: 750;
    }

    .aa-pricing-icon {
        display: grid;
        width: 68px;
        height: 68px;
        flex: 0 0 68px;
        place-items: center;
        border-radius: 999px;
        background: linear-gradient(135deg, #f4e9ff, #fff);
        color: #6f2dbd;
    }

    .aa-pricing-icon.is-plus,
    .aa-pricing-icon.is-ultimate {
        background: linear-gradient(135deg, #fff3c4, #ffe9a8);
        color: #6f2dbd;
    }

    .aa-pricing-icon svg {
        width: 34px;
        height: 34px;
    }

    .aa-pricing-price {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin: 30px 0 0;
        color: #15132f;
        font-size: clamp(28px, 3.1vw, 44px);
        font-weight: 950;
        letter-spacing: -.035em;
        line-height: 1;
    }

    .aa-pricing-period {
        color: #26213f;
        font-size: 15px;
        font-weight: 850;
        letter-spacing: 0;
    }

    .aa-pricing-discount {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin-top: 12px;
        color: #8b8a96;
        font-size: 14px;
        font-weight: 800;
    }

    .aa-pricing-discount s {
        text-decoration-thickness: 2px;
    }

    .aa-pricing-badge {
        display: inline-flex;
        align-items: center;
        min-height: 26px;
        border-radius: 999px;
        background: #f1e7ff;
        color: #6f2dbd;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 950;
    }

    .aa-pricing-card.is-ultimate .aa-pricing-badge {
        background: #fff2c9;
        color: #b06d00;
    }

    .aa-pricing-desc {
        min-height: 68px;
        margin: 18px 0 22px;
        color: #34314c;
        font-size: 14px;
        font-weight: 650;
        line-height: 1.75;
    }

    .aa-pricing-rule {
        height: 1px;
        margin: 0 0 18px;
        background: linear-gradient(90deg, transparent, rgba(36, 28, 63, .17), transparent);
    }

    .aa-pricing-feature-title {
        margin: 0 0 14px;
        color: #17152c;
        font-size: 14px;
        font-weight: 950;
    }

    .aa-pricing-features {
        display: grid;
        gap: 12px;
        margin: 0;
        padding: 0 0 34px;
        list-style: none;
    }

    .aa-pricing-features li,
    .aa-compare-legend li,
    .aa-detail-list li,
    .aa-trust-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .aa-check,
    .aa-crown,
    .aa-info,
    .aa-minus {
        display: inline-grid;
        width: 18px;
        height: 18px;
        flex: 0 0 18px;
        place-items: center;
        border-radius: 999px;
        margin-top: 2px;
    }

    .aa-check {
        background: #e7d9ff;
        color: #6f2dbd;
    }

    .aa-crown {
        background: #fff0bf;
        color: #c48109;
    }

    .aa-info {
        color: #6f2dbd;
    }

    .aa-minus {
        color: #625f78;
    }

    .aa-pricing-card.is-starter .aa-check,
    .aa-pricing-card.is-ultimate .aa-check {
        background: #fff0bf;
        color: #d08a00;
    }

    .aa-check svg,
    .aa-crown svg,
    .aa-info svg,
    .aa-minus svg {
        width: 100%;
        height: 100%;
    }

    .aa-pricing-features span:last-child,
    .aa-detail-list span:last-child {
        color: #221d38;
        font-size: 14px;
        font-weight: 750;
        line-height: 1.45;
    }

    .aa-pricing-action {
        display: inline-flex;
        min-height: 58px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(111, 45, 189, .34);
        border-radius: 10px;
        margin-top: auto;
        color: #4c267e;
        font-size: 15px;
        font-weight: 950;
        padding: 0 18px;
        text-decoration: none;
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
    }

    .aa-pricing-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(111, 45, 189, .14);
    }

    .aa-pricing-action.is-primary {
        border-color: transparent;
        background: linear-gradient(135deg, #6f2dbd, #f2ad26);
        color: #fff;
        box-shadow: 0 18px 38px rgba(111, 45, 189, .22);
    }

    .aa-pricing-action.is-gold {
        border-color: transparent;
        background: linear-gradient(135deg, #df9700, #ffc821);
        color: #fff;
    }

    .aa-pricing-action.is-attention {
        border-color: transparent;
        background: #7c3aed;
        color: #fff;
    }

    .aa-pricing-action.is-disabled {
        cursor: default;
        border-color: rgba(148, 163, 184, .24);
        background: #f3f4f6;
        color: #8b8a96;
        box-shadow: none;
        pointer-events: none;
    }

    .aa-trust-bar,
    .aa-compare-section,
    .aa-interaction-grid > *,
    .aa-bottom-cta {
        border: 1px solid var(--aa-plan-line);
        border-radius: 16px;
        background: rgba(255, 255, 255, .82);
        box-shadow: 0 16px 45px rgba(37, 28, 63, .06);
    }

    .aa-trust-bar {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0;
        margin-top: 0;
        overflow: hidden;
    }

    .aa-trust-item {
        align-items: center;
        border-right: 1px solid rgba(113, 75, 130, .12);
        padding: 18px 24px;
    }

    .aa-trust-item:last-child {
        border-right: 0;
    }

    .aa-trust-icon {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        place-items: center;
        border-radius: 12px;
        color: #6f2dbd;
    }

    .aa-trust-icon svg {
        width: 26px;
        height: 26px;
    }

    .aa-trust-title {
        color: #16142d;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-trust-copy {
        margin-top: 2px;
        color: #625f78;
        font-size: 12px;
        font-weight: 650;
    }

    .aa-compare-section {
        display: grid;
        grid-template-columns: 300px minmax(0, 1fr);
        gap: 24px;
        margin-top: 26px;
        padding: 28px;
    }

    .aa-compare-side h2,
    .aa-interaction-title,
    .aa-bottom-title {
        margin: 0;
        color: #4c267e;
        font-size: clamp(22px, 2.2vw, 30px);
        font-weight: 950;
        letter-spacing: -.03em;
    }

    .aa-compare-side p,
    .aa-bottom-copy {
        margin: 8px 0 0;
        color: #625f78;
        font-size: 14px;
        font-weight: 650;
        line-height: 1.7;
    }

    .aa-compare-legend {
        display: grid;
        gap: 12px;
        margin: 54px 0 0;
        padding: 0;
        list-style: none;
        color: #393351;
        font-size: 13px;
        font-weight: 750;
    }

    .aa-compare-link {
        display: inline-flex;
        margin-top: 58px;
        color: #4c267e;
        font-size: 14px;
        font-weight: 950;
        text-decoration: none;
    }

    .aa-compare-scroll {
        overflow-x: auto;
    }

    .aa-compare-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
        color: #231f3a;
        font-size: 13px;
    }

    .aa-compare-table th,
    .aa-compare-table td {
        border-bottom: 1px solid rgba(113, 75, 130, .14);
        padding: 12px 14px;
        text-align: center;
        vertical-align: middle;
    }

    .aa-compare-table th:first-child,
    .aa-compare-table td:first-child {
        text-align: left;
    }

    .aa-compare-table th {
        color: #4c267e;
        font-weight: 950;
    }

    .aa-compare-table .is-plus-col {
        background: rgba(244, 237, 255, .76);
    }

    .aa-compare-table .is-ultimate-col {
        background: rgba(255, 243, 214, .58);
        color: #b06d00;
    }

    .aa-compare-feature {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        font-weight: 750;
    }

    .aa-status-dot {
        display: inline-grid;
        width: 20px;
        height: 20px;
        place-items: center;
        margin: 0 auto;
        border-radius: 999px;
        color: #6f2dbd;
    }

    .aa-status-dot svg {
        width: 18px;
        height: 18px;
    }

    .aa-interaction-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
        gap: 22px;
        margin-top: 30px;
        align-items: stretch;
    }

    .aa-detail-demo,
    .aa-faq-panel {
        padding: 22px;
    }

    .aa-interaction-kicker {
        margin: 0 0 12px;
        color: #4c267e;
        font-size: 13px;
        font-weight: 950;
        letter-spacing: .08em;
        text-align: center;
        text-transform: uppercase;
    }

    .aa-detail-demo {
        display: grid;
        grid-template-columns: 170px minmax(0, 1fr);
        min-height: 380px;
        padding: 0;
        overflow: hidden;
    }

    .aa-detail-tabs {
        display: grid;
        align-content: start;
        gap: 8px;
        border-right: 1px solid rgba(113, 75, 130, .12);
        padding: 24px 14px;
    }

    .aa-detail-tab {
        display: flex;
        align-items: center;
        gap: 9px;
        border: 1px solid transparent;
        border-radius: 10px;
        background: transparent;
        color: #514a66;
        cursor: pointer;
        font: inherit;
        font-size: 12px;
        font-weight: 850;
        padding: 12px 10px;
        text-align: left;
    }

    .aa-detail-tab.is-active {
        border-color: rgba(111, 45, 189, .52);
        background: rgba(244, 237, 255, .78);
        color: #4c267e;
    }

    .aa-detail-body {
        padding: 28px;
    }

    .aa-detail-panel {
        display: none;
    }

    .aa-detail-panel.is-active {
        display: block;
    }

    .aa-detail-panel h3 {
        margin: 0;
        color: #16142d;
        font-size: 25px;
        font-weight: 950;
        letter-spacing: -.035em;
        line-height: 1.12;
    }

    .aa-detail-panel p {
        margin: 12px 0 16px;
        color: #625f78;
        font-size: 13px;
        font-weight: 650;
        line-height: 1.7;
    }

    .aa-detail-list {
        display: grid;
        gap: 10px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .aa-photobooth-strip {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 18px;
        border-radius: 12px;
        background: #f8f1e8;
        padding: 12px;
    }

    .aa-photobooth-strip span {
        display: grid;
        min-height: 82px;
        place-items: center;
        border: 1px solid rgba(113, 75, 130, .12);
        border-radius: 10px;
        background: #fff;
        color: #4c267e;
        font-size: 12px;
        font-weight: 950;
        text-align: center;
    }

    .aa-faq-panel {
        display: grid;
        gap: 10px;
        align-content: start;
    }

    .aa-plans-faq-item {
        overflow: hidden;
        border: 1px solid rgba(113, 75, 130, .12);
        border-radius: 12px;
        background: rgba(255, 255, 255, .86);
    }

    .aa-plans-faq-btn {
        display: flex;
        width: 100%;
        min-height: 54px;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        border: 0;
        background: transparent;
        color: #16142d;
        cursor: pointer;
        font: inherit;
        font-size: 13px;
        font-weight: 900;
        padding: 14px 16px;
        text-align: left;
    }

    .aa-plans-faq-icon {
        font-size: 18px;
        font-weight: 900;
    }

    .aa-plans-faq-content {
        display: none;
        padding: 0 16px 16px;
        color: #625f78;
        font-size: 13px;
        font-weight: 650;
        line-height: 1.7;
    }

    .aa-plans-faq-item.is-open .aa-plans-faq-content {
        display: block;
    }

    .aa-bottom-cta {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 20px;
        align-items: center;
        margin-top: 24px;
        padding: 20px 28px;
    }

    .aa-bottom-points {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        color: #625f78;
        font-size: 13px;
        font-weight: 750;
    }

    .aa-modal {
        position: fixed;
        inset: 0;
        z-index: 80;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .aa-modal.is-open {
        display: flex;
    }

    .aa-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(21, 19, 47, .5);
        backdrop-filter: blur(8px);
    }

    .aa-modal-card {
        position: relative;
        width: min(100%, 780px);
        max-height: calc(100vh - 40px);
        overflow: auto;
        border: 1px solid rgba(113, 75, 130, .2);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 32px 80px rgba(21, 19, 47, .22);
    }

    .aa-modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border: 1px solid rgba(113, 75, 130, .16);
        border-radius: 999px;
        background: #fff;
        color: #16142d;
        cursor: pointer;
        font-size: 24px;
        line-height: 1;
    }

    html[data-aa-public-theme="dark"] .aa-plans-page {
        --aa-plan-ink: #f8fafc;
        --aa-plan-muted: #cbd5e1;
        --aa-plan-line: rgba(196, 181, 253, .18);
        --aa-plan-card: rgba(15, 23, 42, .9);
        background:
            radial-gradient(circle at 12% 6%, rgba(20, 184, 166, .16), transparent 28rem),
            radial-gradient(circle at 88% 10%, rgba(143, 101, 223, .22), transparent 30rem),
            linear-gradient(180deg, #07111d 0%, #0a0f1b 42%, #070b12 100%);
        color: #f8fafc;
    }

    html[data-aa-public-theme="dark"] .aa-plans-title,
    html[data-aa-public-theme="dark"] .aa-product-plans-title,
    html[data-aa-public-theme="dark"] .aa-trust-title,
    html[data-aa-public-theme="dark"] .aa-compare-side h2,
    html[data-aa-public-theme="dark"] .aa-interaction-title,
    html[data-aa-public-theme="dark"] .aa-bottom-title,
    html[data-aa-public-theme="dark"] .aa-detail-panel h3 {
        color: #f8fafc;
        text-shadow: 0 2px 18px rgba(0, 0, 0, .28);
    }

    html[data-aa-public-theme="dark"] .aa-plans-title span {
        background: linear-gradient(100deg, #fbbf24, #f0abfc 48%, #a78bfa);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    html[data-aa-public-theme="dark"] .aa-plans-subtitle,
    html[data-aa-public-theme="dark"] .aa-product-plans-subtitle,
    html[data-aa-public-theme="dark"] .aa-trust-copy,
    html[data-aa-public-theme="dark"] .aa-compare-side p,
    html[data-aa-public-theme="dark"] .aa-bottom-copy,
    html[data-aa-public-theme="dark"] .aa-bottom-points,
    html[data-aa-public-theme="dark"] .aa-detail-panel p,
    html[data-aa-public-theme="dark"] .aa-plans-faq-content {
        color: #cbd5e1;
    }

    html[data-aa-public-theme="dark"] .aa-pricing-card,
    html[data-aa-public-theme="dark"] .aa-pricing-card.is-plus,
    html[data-aa-public-theme="dark"] .aa-modal-card {
        border-color: rgba(196, 181, 253, .18);
        background:
            radial-gradient(circle at 50% -14%, rgba(143, 101, 223, .18), transparent 18rem),
            rgba(15, 23, 42, .9);
        box-shadow: 0 24px 64px rgba(0, 0, 0, .24);
    }

    html[data-aa-public-theme="dark"] .aa-pricing-card:hover {
        border-color: rgba(196, 181, 253, .38);
        box-shadow: 0 30px 78px rgba(0, 0, 0, .34);
    }

    html[data-aa-public-theme="dark"] .aa-pricing-name,
    html[data-aa-public-theme="dark"] .aa-pricing-price,
    html[data-aa-public-theme="dark"] .aa-pricing-feature-title,
    html[data-aa-public-theme="dark"] .aa-plans-faq-btn {
        color: #f8fafc;
    }

    html[data-aa-public-theme="dark"] .aa-pricing-tagline,
    html[data-aa-public-theme="dark"] .aa-pricing-period,
    html[data-aa-public-theme="dark"] .aa-pricing-desc,
    html[data-aa-public-theme="dark"] .aa-pricing-features span:last-child {
        color: #cbd5e1;
    }

    html[data-aa-public-theme="dark"] .aa-pricing-icon,
    html[data-aa-public-theme="dark"] .aa-modal-close {
        border-color: rgba(196, 181, 253, .18);
        background: rgba(124, 58, 237, .18);
        color: #c4b5fd;
    }

    html[data-aa-public-theme="dark"] .aa-pricing-rule {
        background: linear-gradient(90deg, transparent, rgba(196, 181, 253, .22), transparent);
    }

    html[data-aa-public-theme="dark"] .aa-trust-bar,
    html[data-aa-public-theme="dark"] .aa-compare-section,
    html[data-aa-public-theme="dark"] .aa-interaction-grid > *,
    html[data-aa-public-theme="dark"] .aa-bottom-cta,
    html[data-aa-public-theme="dark"] .aa-plans-faq-item {
        border-color: rgba(196, 181, 253, .18);
        background: rgba(15, 23, 42, .72);
        box-shadow: 0 18px 58px rgba(0, 0, 0, .22);
    }

    html[data-aa-public-theme="dark"] .aa-trust-item,
    html[data-aa-public-theme="dark"] .aa-detail-tabs,
    html[data-aa-public-theme="dark"] .aa-compare-table th,
    html[data-aa-public-theme="dark"] .aa-compare-table td,
    html[data-aa-public-theme="dark"] .aa-photobooth-strip span {
        border-color: rgba(196, 181, 253, .14);
    }

    html[data-aa-public-theme="dark"] .aa-compare-table {
        color: #dbe4f0;
    }

    html[data-aa-public-theme="dark"] .aa-compare-table th,
    html[data-aa-public-theme="dark"] .aa-compare-link,
    html[data-aa-public-theme="dark"] .aa-interaction-kicker {
        color: #c4b5fd;
    }

    html[data-aa-public-theme="dark"] .aa-compare-legend,
    html[data-aa-public-theme="dark"] .aa-detail-tab {
        color: #cbd5e1;
    }

    html[data-aa-public-theme="dark"] .aa-detail-tab.is-active,
    html[data-aa-public-theme="dark"] .aa-compare-table .is-plus-col {
        border-color: rgba(167, 139, 250, .42);
        background: rgba(124, 58, 237, .18);
        color: #f8fafc;
    }

    html[data-aa-public-theme="dark"] .aa-compare-table .is-ultimate-col {
        background: rgba(251, 191, 36, .12);
        color: #fde68a;
    }

    html[data-aa-public-theme="dark"] .aa-photobooth-strip {
        background: rgba(7, 11, 18, .7);
    }

    html[data-aa-public-theme="dark"] .aa-photobooth-strip span {
        background: rgba(15, 23, 42, .82);
        color: #f8fafc;
    }

    @media (max-width: 1100px) {
        .aa-pricing-grid,
        .aa-trust-bar,
        .aa-interaction-grid {
            grid-template-columns: 1fr 1fr;
        }

        .aa-compare-section {
            grid-template-columns: 1fr;
        }

        .aa-compare-legend,
        .aa-compare-link {
            margin-top: 18px;
        }
    }

    @media (max-width: 760px) {
        .aa-plans-main {
            width: min(100% - 24px, 1540px);
            padding-bottom: 72px;
        }

        .aa-pricing-grid,
        .aa-trust-bar,
        .aa-interaction-grid,
        .aa-bottom-cta,
        .aa-detail-demo {
            grid-template-columns: 1fr;
        }

        .aa-trust-item {
            border-right: 0;
            border-bottom: 1px solid rgba(113, 75, 130, .12);
        }

        .aa-detail-tabs {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            border-right: 0;
            border-bottom: 1px solid rgba(113, 75, 130, .12);
        }

        .aa-bottom-cta {
            position: static;
            padding: 16px;
        }

        .aa-pricing-grid {
            gap: 46px;
        }

        .aa-pricing-card.is-plus {
            margin-top: 10px;
        }
    }
    </style>
</head>

<body class="aa-app-ui aa-plans-page aa-public-theme-page">
    <?= view('components/public_site_header', ['active' => 'plans']) ?>

    <main class="aa-plans-main">
        <section class="aa-plans-hero" aria-labelledby="aaPlansTitle">
            <h1 class="aa-plans-title" id="aaPlansTitle">Pilih paket <span>AdaAcara</span> yang cocok untukmu</h1>
            <p class="aa-plans-subtitle">Semua paket memberi kamu akses ke AdaAcara Studio. Buat undangan digital, Digital Photobooth, dan pengalaman acara digital dengan mudah.</p>
        </section>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="aa-plans-alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif ?>

        <?php if ($creatorBlocksMembership): ?>
            <div class="aa-plans-alert is-info">
                Akun kamu sedang berada di flow Creator<?= $creatorFlowStatus === 'active' ? ' aktif' : ' dan menunggu approve admin' ?>, jadi pembelian paket membership dinonaktifkan.
            </div>
        <?php endif ?>

        <?php if ($displayPlans === [] && $productPlans === []): ?>
            <div class="aa-plans-alert is-info">Belum ada paket aktif.</div>
        <?php else: ?>
            <?php if ($displayPlans !== []): ?>
            <section class="aa-pricing-grid" aria-label="Paket AdaAcara">
                <?php foreach ($displayPlans as $card): ?>
                    <?php
                        $toneClass = 'is-' . preg_replace('/[^a-z0-9-]/', '', (string) $card['tone']);
                        $actionClass = 'aa-pricing-action';
                        if ($card['actionState'] === 'disabled') {
                            $actionClass .= ' is-disabled';
                        } elseif ($card['actionState'] === 'attention') {
                            $actionClass .= ' is-attention';
                        } elseif ($card['rank'] === 2) {
                            $actionClass .= ' is-primary';
                        } elseif ($card['rank'] === 3) {
                            $actionClass .= ' is-gold';
                        }
                    ?>
                    <article class="aa-pricing-card <?= esc($toneClass, 'attr') ?>">
                        <?php if ($card['isPopular']): ?>
                            <span class="aa-pricing-popular">Paling Populer</span>
                        <?php endif ?>
                        <div class="aa-pricing-head">
                            <div>
                                <h2 class="aa-pricing-name"><?= esc($card['name']) ?></h2>
                                <p class="aa-pricing-tagline"><?= esc($card['tagline']) ?></p>
                            </div>
                            <span class="aa-pricing-icon <?= $card['rank'] === 2 ? 'is-plus' : ($card['rank'] === 3 ? 'is-ultimate' : '') ?>" aria-hidden="true">
                                <?php if ($card['icon'] === 'crown'): ?>
                                    <?= $crownSvg ?>
                                <?php elseif ($card['icon'] === 'diamond'): ?>
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 3h12l4 6-10 12L2 9l4-6Z" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/><path d="M2 9h20M8 3l4 18 4-18" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round"/></svg>
                                <?php else: ?>
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="2.2"/><path d="M4 21a8 8 0 0 1 16 0" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                                <?php endif ?>
                            </span>
                        </div>

                        <div class="aa-pricing-price">
                            <span>Rp <?= number_format((int) $card['price'], 0, ',', '.') ?></span>
                            <span class="aa-pricing-period">/ <?= esc($card['period']) ?></span>
                        </div>
                        <?php if ($card['hasDiscount']): ?>
                            <div class="aa-pricing-discount">
                                <s>Rp <?= number_format((int) $card['compareAtPrice'], 0, ',', '.') ?></s>
                                <span class="aa-pricing-badge">Hemat <?= esc((string) $card['discountPercent']) ?>%</span>
                            </div>
                        <?php endif ?>
                        <p class="aa-pricing-desc"><?= esc($card['description']) ?></p>
                        <div class="aa-pricing-rule"></div>
                        <p class="aa-pricing-feature-title"><?= esc($card['featureIntro']) ?></p>
                        <ul class="aa-pricing-features">
                            <?php foreach ($card['features'] as $feature): ?>
                                <li>
                                    <span class="<?= $card['rank'] > 1 ? 'aa-check' : 'aa-check' ?>" aria-hidden="true"><?= $checkSvg ?></span>
                                    <span><?= esc($feature) ?></span>
                                </li>
                            <?php endforeach ?>
                        </ul>

                        <?php if ($card['actionDisabled']): ?>
                            <span class="<?= esc($actionClass, 'attr') ?>"><?= esc($card['actionLabel']) ?></span>
                        <?php else: ?>
                            <a class="<?= esc($actionClass, 'attr') ?>" href="<?= esc((string) $card['actionHref'], 'attr') ?>"><?= esc($card['actionLabel']) ?></a>
                        <?php endif ?>
                    </article>
                <?php endforeach ?>
            </section>
            <?php endif ?>

            <?php if ($productPlans !== []): ?>
                <section class="aa-product-plans" aria-label="Produk Website dan Tools">
                    <div class="aa-product-plans-head">
                        <div>
                            <h2 class="aa-product-plans-title">Produk Website & Tools</h2>
                            <p class="aa-product-plans-subtitle">Business Profile dan Galeri Klien Fotografer dibeli terpisah dari membership undangan. Akses aktif otomatis setelah pembayaran berhasil.</p>
                        </div>
                    </div>
                    <div class="aa-pricing-grid">
                        <?php foreach ($productPlans as $product): ?>
                            <?php
                                $actionClass = 'aa-pricing-action is-primary';
                                if ($product['state'] === 'disabled') {
                                    $actionClass = 'aa-pricing-action is-disabled';
                                } elseif ($product['state'] === 'attention') {
                                    $actionClass = 'aa-pricing-action is-attention';
                                }
                            ?>
                            <article class="aa-pricing-card">
                                <div class="aa-pricing-head">
                                    <div>
                                        <h2 class="aa-pricing-name"><?= esc($product['title']) ?></h2>
                                        <p class="aa-pricing-tagline"><?= esc($product['tagline']) ?></p>
                                    </div>
                                    <span class="aa-pricing-icon" aria-hidden="true">
                                        <?= $crownSvg ?>
                                    </span>
                                </div>
                                <div class="aa-pricing-price">
                                    <span>Rp <?= number_format((int) $product['price'], 0, ',', '.') ?></span>
                                    <span class="aa-pricing-period">/ <?= esc($product['period']) ?></span>
                                </div>
                                <p class="aa-pricing-desc"><?= esc($product['description']) ?></p>
                                <div class="aa-pricing-rule"></div>
                                <p class="aa-pricing-feature-title">Yang kamu dapatkan:</p>
                                <ul class="aa-pricing-features">
                                    <?php foreach ($product['features'] as $feature): ?>
                                        <li>
                                            <span class="aa-check" aria-hidden="true"><?= $checkSvg ?></span>
                                            <span><?= esc($feature) ?></span>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                                <?php if ($product['disabled']): ?>
                                    <span class="<?= esc($actionClass, 'attr') ?>"><?= esc($product['button']) ?></span>
                                <?php else: ?>
                                    <a class="<?= esc($actionClass, 'attr') ?>" href="<?= esc((string) $product['href'], 'attr') ?>"><?= esc($product['button']) ?></a>
                                <?php endif ?>
                            </article>
                        <?php endforeach ?>
                    </div>
                </section>
            <?php endif ?>

            <section class="aa-trust-bar" aria-label="Keunggulan AdaAcara">
                <?php foreach ([
                    ['Aman & terpercaya', 'Data kamu aman di cloud'],
                    ['Tanpa instalasi', 'Langsung di browser'],
                    ['Dukungan cepat', 'Tim support siap membantu'],
                    ['Update fitur rutin', 'Selalu ada yang baru'],
                ] as $trust): ?>
                    <article class="aa-trust-item">
                        <span class="aa-trust-icon" aria-hidden="true"><?= $checkSvg ?></span>
                        <div>
                            <div class="aa-trust-title"><?= esc($trust[0]) ?></div>
                            <div class="aa-trust-copy"><?= esc($trust[1]) ?></div>
                        </div>
                    </article>
                <?php endforeach ?>
            </section>

            <section class="aa-compare-section" id="compare">
                <aside class="aa-compare-side">
                    <h2>Bandingkan Fitur</h2>
                    <p>Semua perbedaan utama antar paket.</p>
                    <ul class="aa-compare-legend">
                        <li><span class="aa-check"><?= $checkSvg ?></span><span>Tersedia</span></li>
                        <li><span class="aa-crown"><?= $crownSvg ?></span><span>Premium / Lebih Lengkap</span></li>
                        <li><span class="aa-minus"><?= $minusSvg ?></span><span>Tidak tersedia</span></li>
                        <li><span class="aa-info"><?= $infoSvg ?></span><span>Terbatas</span></li>
                    </ul>
                    <a class="aa-compare-link" href="#feature-detail" data-open-feature-modal>Lihat semua fitur -></a>
                </aside>
                <div class="aa-compare-scroll">
                    <table class="aa-compare-table">
                        <thead>
                            <tr>
                                <th>Fitur</th>
                                <?php foreach ($displayPlans as $card): ?>
                                    <th class="<?= $card['rank'] === 2 ? 'is-plus-col' : ($card['rank'] === 3 ? 'is-ultimate-col' : '') ?>">
                                        <?= esc($card['name']) ?><br>
                                        <small>Rp <?= number_format((int) $card['price'], 0, ',', '.') ?> / <?= esc($card['period']) ?></small>
                                    </th>
                                <?php endforeach ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comparisonRows as $row): ?>
                                <tr>
                                    <td><span class="aa-compare-feature"><span class="aa-info"><?= $infoSvg ?></span><?= esc($row[0]) ?></span></td>
                                    <?php foreach ($displayPlans as $card): ?>
                                        <?php
                                            $value = $row[0] === 'Harga'
                                                ? 'Rp ' . number_format((int) $card['price'], 0, ',', '.') . ' / ' . $card['period']
                                                : ($row[0] === 'Akses Membership'
                                                    ? $card['period']
                                                    : ($row[1][$card['rank']] ?? 'minus'));
                                        ?>
                                        <td class="<?= $card['rank'] === 2 ? 'is-plus-col' : ($card['rank'] === 3 ? 'is-ultimate-col' : '') ?>">
                                            <?php if ($value === 'check'): ?>
                                                <span class="aa-status-dot"><?= $checkSvg ?></span>
                                            <?php elseif ($value === 'minus'): ?>
                                                <span class="aa-status-dot"><?= $minusSvg ?></span>
                                            <?php elseif ($value === 'limited'): ?>
                                                <span class="aa-status-dot"><?= $infoSvg ?></span>
                                            <?php else: ?>
                                                <strong><?= esc((string) $value) ?></strong>
                                            <?php endif ?>
                                        </td>
                                    <?php endforeach ?>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="aa-interaction-grid" id="feature-detail">
                <div class="aa-detail-demo">
                    <div class="aa-detail-tabs" role="tablist" aria-label="Detail fitur">
                        <?php foreach ($detailTabs as $key => $tab): ?>
                            <button class="aa-detail-tab <?= $key === 'photobooth' ? 'is-active' : '' ?>" type="button" data-detail-tab="<?= esc($key, 'attr') ?>">
                                <span class="aa-info"><?= $infoSvg ?></span>
                                <span><?= esc($tab['label']) ?></span>
                            </button>
                        <?php endforeach ?>
                    </div>
                    <div class="aa-detail-body">
                        <?php foreach ($detailTabs as $key => $tab): ?>
                            <article class="aa-detail-panel <?= $key === 'photobooth' ? 'is-active' : '' ?>" data-detail-panel="<?= esc($key, 'attr') ?>">
                                <h3><?= esc($tab['title']) ?></h3>
                                <p><?= esc($tab['body']) ?></p>
                                <ul class="aa-detail-list">
                                    <?php foreach ($tab['items'] as $item): ?>
                                        <li><span class="aa-check"><?= $checkSvg ?></span><span><?= esc($item) ?></span></li>
                                    <?php endforeach ?>
                                </ul>
                                <?php if ($key === 'photobooth'): ?>
                                    <div class="aa-photobooth-strip" aria-hidden="true">
                                        <span>QR Scan</span>
                                        <span>Frame Foto</span>
                                        <span>Print</span>
                                    </div>
                                <?php endif ?>
                            </article>
                        <?php endforeach ?>
                    </div>
                </div>

                <div class="aa-faq-panel">
                    <p class="aa-interaction-kicker">FAQ Interaktif</p>
                    <?php foreach ($plansFaqs as $index => $faq): ?>
                        <article class="aa-plans-faq-item <?= $index === 2 ? 'is-open' : '' ?>">
                            <button class="aa-plans-faq-btn" type="button" aria-expanded="<?= $index === 2 ? 'true' : 'false' ?>">
                                <span><?= esc($faq[0]) ?></span>
                                <span class="aa-plans-faq-icon" aria-hidden="true"><?= $index === 2 ? '-' : '+' ?></span>
                            </button>
                            <div class="aa-plans-faq-content"><?= esc($faq[1]) ?></div>
                        </article>
                    <?php endforeach ?>
                </div>
            </section>

            <section class="aa-bottom-cta">
                <div>
                    <h2 class="aa-bottom-title">Pilih paket sesuai kebutuhan acaramu.</h2>
                    <p class="aa-bottom-copy">Mulai dari paket yang paling sesuai untuk Undangan Digital, Photobooth Digital, atau project customer. Upgrade bisa dilakukan saat kebutuhanmu bertambah.</p>
                    <div class="aa-bottom-points">
                        <span>Upgrade saat dibutuhkan</span>
                        <span>Data & project tetap aman</span>
                        <span>Checkout mengikuti metode pembayaran aktif</span>
                    </div>
                </div>
                <a class="aa-pricing-action is-primary" href="<?= esc($fallbackStartUrl, 'attr') ?>">Mulai Sekarang -></a>
            </section>
        <?php endif ?>
    </main>

    <div class="aa-modal" id="aaFeatureModal" aria-hidden="true">
        <div class="aa-modal-backdrop" data-close-feature-modal></div>
        <div class="aa-modal-card" role="dialog" aria-modal="true" aria-labelledby="aaFeatureModalTitle">
            <button class="aa-modal-close" type="button" aria-label="Tutup" data-close-feature-modal>⛌</button>
            <div class="aa-detail-demo">
                <div class="aa-detail-tabs" role="tablist" aria-label="Modal detail fitur">
                    <?php foreach ($detailTabs as $key => $tab): ?>
                        <button class="aa-detail-tab <?= $key === 'photobooth' ? 'is-active' : '' ?>" type="button" data-modal-detail-tab="<?= esc($key, 'attr') ?>">
                            <span class="aa-info"><?= $infoSvg ?></span>
                            <span><?= esc($tab['label']) ?></span>
                        </button>
                    <?php endforeach ?>
                </div>
                <div class="aa-detail-body">
                    <?php foreach ($detailTabs as $key => $tab): ?>
                        <article class="aa-detail-panel <?= $key === 'photobooth' ? 'is-active' : '' ?>" data-modal-detail-panel="<?= esc($key, 'attr') ?>">
                            <h3<?= $key === 'photobooth' ? ' id="aaFeatureModalTitle"' : '' ?>><?= esc($tab['title']) ?></h3>
                            <p><?= esc($tab['body']) ?></p>
                            <ul class="aa-detail-list">
                                <?php foreach ($tab['items'] as $item): ?>
                                    <li><span class="aa-check"><?= $checkSvg ?></span><span><?= esc($item) ?></span></li>
                                <?php endforeach ?>
                            </ul>
                            <?php if ($key === 'photobooth'): ?>
                                <div class="aa-photobooth-strip" aria-hidden="true">
                                    <span>QR Scan</span>
                                    <span>Memories</span>
                                    <span>Print</span>
                                </div>
                            <?php endif ?>
                        </article>
                    <?php endforeach ?>
                </div>
            </div>
        </div>
    </div>

    <?= view('components/site_footer') ?>

    <script>
    document.addEventListener('click', function(event) {
        const faqButton = event.target.closest('.aa-plans-faq-btn');
        if (faqButton) {
            const item = faqButton.closest('.aa-plans-faq-item');
            const icon = faqButton.querySelector('.aa-plans-faq-icon');
            if (item) {
                const isOpen = item.classList.toggle('is-open');
                faqButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                if (icon) {
                    icon.textContent = isOpen ? '-' : '+';
                }
            }
            return;
        }

        const tab = event.target.closest('[data-detail-tab]');
        if (tab) {
            const key = tab.getAttribute('data-detail-tab');
            document.querySelectorAll('[data-detail-tab]').forEach(function(node) {
                node.classList.toggle('is-active', node === tab);
            });
            document.querySelectorAll('[data-detail-panel]').forEach(function(panel) {
                panel.classList.toggle('is-active', panel.getAttribute('data-detail-panel') === key);
            });
            return;
        }

        const modalTab = event.target.closest('[data-modal-detail-tab]');
        if (modalTab) {
            const key = modalTab.getAttribute('data-modal-detail-tab');
            document.querySelectorAll('[data-modal-detail-tab]').forEach(function(node) {
                node.classList.toggle('is-active', node === modalTab);
            });
            document.querySelectorAll('[data-modal-detail-panel]').forEach(function(panel) {
                panel.classList.toggle('is-active', panel.getAttribute('data-modal-detail-panel') === key);
            });
            return;
        }

        if (event.target.closest('[data-open-feature-modal]')) {
            const modal = document.getElementById('aaFeatureModal');
            if (modal) {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            }
            return;
        }

        if (event.target.closest('[data-close-feature-modal]')) {
            const modal = document.getElementById('aaFeatureModal');
            if (modal) {
                modal.classList.remove('is-open');
                modal.setAttribute('aria-hidden', 'true');
            }
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key !== 'Escape') {
            return;
        }
        const modal = document.getElementById('aaFeatureModal');
        if (modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        }
    });
    </script>
</body>

</html>
