<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Verifikasi Email - Ada Acara') ?></title>
    <?= view('components/noindex_meta') ?>
    <?= view('components/modern_alerts') ?>
    <?= view('components/app_ui_assets') ?>
    <link rel="icon" type="image/png" href="https://adaacara.com/assets/img/logo2.png">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #eef8f5 0%, #ffffff 52%, #f4fbf9 100%);
            color: #0f172a;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .auth-shell {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 32px 18px;
        }
        .auth-card {
            width: min(100%, 500px);
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 28px;
            background: rgba(255, 255, 255, .9);
            box-shadow: 0 24px 70px rgba(15, 23, 42, .10);
            padding: 30px;
        }
        .auth-logo img {
            height: 38px;
            width: auto;
            object-fit: contain;
        }
        h1 {
            margin: 26px 0 10px;
            font-size: 30px;
            line-height: 1.08;
            letter-spacing: 0;
        }
        p {
            margin: 0;
            color: #64748b;
            font-size: 15px;
            line-height: 1.6;
            font-weight: 650;
        }
        .alert {
            margin-top: 18px;
            border-radius: 18px;
            border: 1px solid rgba(225, 29, 72, .16);
            background: #fff1f2;
            color: #9f1239;
            padding: 13px 15px;
            font-size: 13px;
            line-height: 1.5;
            font-weight: 750;
        }
        .alert.is-success {
            border-color: rgba(5, 150, 105, .16);
            background: #ecfdf5;
            color: #047857;
        }
        form {
            display: grid;
            gap: 14px;
            margin-top: 24px;
        }
        input {
            width: 100%;
            min-height: 54px;
            box-sizing: border-box;
            border: 1px solid rgba(15, 23, 42, .12);
            border-radius: 20px;
            background: #fff;
            color: #0f172a;
            font-size: 15px;
            font-weight: 750;
            outline: 0;
            padding: 0 16px;
        }
        button,
        .back-link {
            display: inline-flex;
            min-height: 54px;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 950;
            text-decoration: none;
        }
        button {
            border: 0;
            background: linear-gradient(135deg, #07966f, #07825f);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 16px 36px rgba(7, 134, 109, .22);
        }
        .back-link {
            margin-top: 14px;
            color: #047857;
        }
    </style>
</head>
<body class="aa-app-ui">
    <main class="auth-shell">
        <section class="auth-card">
            <a class="auth-logo" href="<?= site_url('/') ?>" aria-label="AdaAcara">
                <img src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
            </a>
            <h1>Verifikasi email dulu</h1>
            <p>Kami sudah mengirim link verifikasi. Akun baru belum bisa login sebelum email diverifikasi.</p>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert is-success"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif ?>

            <form action="<?= site_url('verify-email/resend') ?>" method="post">
                <?= csrf_field() ?>
                <input name="email" type="email" value="<?= esc(old('email', $email ?? ''), 'attr') ?>" placeholder="kamu@adaacara.com" autocomplete="email" required>
                <button type="submit">Kirim ulang email verifikasi</button>
            </form>

            <a class="back-link" href="<?= site_url('login') ?>">Kembali ke login</a>
        </section>
    </main>
</body>
</html>
