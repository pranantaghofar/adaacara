<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Reset Password - Ada Acara') ?></title>
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
            width: min(100%, 460px);
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 28px;
            background: rgba(255, 255, 255, .88);
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
        .alert ul {
            margin: 0;
            padding-left: 18px;
        }
        form {
            display: grid;
            gap: 16px;
            margin-top: 24px;
        }
        label {
            color: #102025;
            font-size: 13px;
            font-weight: 900;
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
        input:focus {
            border-color: rgba(7, 134, 109, .58);
            box-shadow: 0 0 0 5px rgba(7, 134, 109, .1);
        }
        button {
            display: inline-flex;
            min-height: 54px;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 20px;
            background: linear-gradient(135deg, #07966f, #07825f);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 16px 36px rgba(7, 134, 109, .22);
            font-size: 14px;
            font-weight: 950;
        }
    </style>
</head>
<body class="aa-app-ui">
    <main class="auth-shell">
        <section class="auth-card">
            <a class="auth-logo" href="<?= site_url('/') ?>" aria-label="AdaAcara">
                <img src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
            </a>
            <h1>Buat password baru</h1>
            <p>Gunakan password minimal 8 karakter agar akun tetap aman.</p>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert">
                    <ul>
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <form action="<?= site_url('reset-password/' . $token) ?>" method="post">
                <?= csrf_field() ?>
                <label for="password">Password baru</label>
                <input id="password" name="password" type="password" autocomplete="new-password" required minlength="8">
                <label for="password_confirm">Ulangi password baru</label>
                <input id="password_confirm" name="password_confirm" type="password" autocomplete="new-password" required minlength="8">
                <button type="submit">Simpan password baru</button>
            </form>
        </section>
    </main>
</body>
</html>
