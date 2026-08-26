<?php
    $title = (string) ($title ?? 'Halaman tidak ditemukan');
    $headline = (string) ($headline ?? 'Halaman tidak ditemukan');
    $message = (string) ($message ?? 'Link yang kamu buka belum aktif, sudah dihapus, atau alamatnya kurang tepat.');
    $code = (string) ($code ?? '404');
    $plansUrl = (string) ($plansUrl ?? site_url('plans'));
    $templatesUrl = (string) ($templatesUrl ?? site_url('templates'));
    $homeUrl = (string) ($homeUrl ?? site_url('/'));
    $primaryLabel = (string) ($primaryLabel ?? 'Lihat Paket Membership');
    $primaryUrl = (string) ($primaryUrl ?? $plansUrl);
    $secondaryLabel = (string) ($secondaryLabel ?? 'Buat Undangan');
    $secondaryUrl = (string) ($secondaryUrl ?? $templatesUrl);
    $note = (string) ($note ?? 'Sudah punya link undangan? Pastikan undangan sudah dipublish dan slug URL ditulis dengan benar.');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> - AdaAcara</title>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <?= view('components/app_ui_assets') ?>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at 18% 16%, rgba(20, 184, 166, .16), transparent 32%),
                radial-gradient(circle at 82% 18%, rgba(168, 85, 247, .14), transparent 34%),
                linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
            color: #0f172a;
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .aa-not-found-page {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 28px 18px;
        }

        .aa-not-found-card {
            width: min(100%, 760px);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .72);
            border-radius: 28px;
            background: rgba(255, 255, 255, .86);
            box-shadow: 0 30px 90px rgba(15, 23, 42, .18);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .aa-not-found-content {
            display: grid;
            gap: 22px;
            padding: clamp(28px, 6vw, 54px);
            text-align: center;
        }

        .aa-not-found-logo {
            display: block;
            width: 74px;
            height: auto;
            margin: 0 auto 4px;
        }

        .aa-not-found-code {
            display: inline-flex;
            width: max-content;
            min-height: 34px;
            align-items: center;
            justify-content: center;
            justify-self: center;
            border: 1px solid #ccfbf1;
            border-radius: 999px;
            background: #f0fdfa;
            padding: 0 14px;
            color: #0f766e;
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        h1 {
            max-width: 620px;
            margin: 0 auto;
            font-size: clamp(32px, 7vw, 58px);
            line-height: 1.02;
            letter-spacing: 0;
        }

        p {
            max-width: 580px;
            margin: 0 auto;
            color: #475569;
            font-size: clamp(15px, 2.5vw, 18px);
            font-weight: 650;
            line-height: 1.7;
        }

        .aa-not-found-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
            margin-top: 4px;
        }

        .aa-not-found-btn {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0 20px;
            font-size: 14px;
            font-weight: 900;
            text-decoration: none;
        }

        .aa-not-found-btn-primary {
            background: #0f172a;
            color: #ffffff;
            box-shadow: 0 16px 30px rgba(15, 23, 42, .18);
        }

        .aa-not-found-btn-secondary {
            border: 1px solid #dbe4ef;
            background: #ffffff;
            color: #0f172a;
        }

        .aa-not-found-note {
            margin-top: 2px;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }
    </style>
</head>
<body class="aa-app-ui">
    <main class="aa-not-found-page">
        <section class="aa-not-found-card" aria-labelledby="aaNotFoundTitle">
            <div class="aa-not-found-content">
                <img class="aa-not-found-logo" src="<?= esc(aa_asset_url('assets/img/adaacara-logo.png'), 'attr') ?>" alt="AdaAcara" width="74" height="29" loading="eager" decoding="async">
                <span class="aa-not-found-code"><?= esc($code) ?></span>
                <h1 id="aaNotFoundTitle"><?= esc($headline) ?></h1>
                <p><?= esc($message) ?></p>
                <div class="aa-not-found-actions">
                    <a class="aa-not-found-btn aa-not-found-btn-primary" href="<?= esc($primaryUrl, 'attr') ?>"><?= esc($primaryLabel) ?></a>
                    <a class="aa-not-found-btn aa-not-found-btn-secondary" href="<?= esc($secondaryUrl, 'attr') ?>"><?= esc($secondaryLabel) ?></a>
                </div>
                <div class="aa-not-found-note">
                    <?= esc($note) ?>
                </div>
                <a class="aa-not-found-btn aa-not-found-btn-secondary" href="<?= esc($homeUrl, 'attr') ?>">Kembali ke Beranda</a>
            </div>
        </section>
    </main>
</body>
</html>
