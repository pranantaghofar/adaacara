<?php
$aaAuthErrors = session()->getFlashdata('errors');
$aaAuthErrors = is_array($aaAuthErrors) ? $aaAuthErrors : [];
$aaAuthFieldLabels = [
    'name' => 'Nama lengkap',
    'email' => 'Email',
    'password' => 'Password',
    'password_confirm' => 'Konfirmasi password',
];
$aaAuthTranslateError = static function ($message, string $field = '') use ($aaAuthFieldLabels): string {
    $text = trim((string) $message);
    $label = $aaAuthFieldLabels[$field] ?? 'Kolom ini';
    $lower = strtolower($text);

    if ($text === '') {
        return '';
    }

    if (str_contains($lower, 'required')) {
        return $label . ' wajib diisi.';
    }

    if (str_contains($lower, 'valid_email') || str_contains($lower, 'valid email')) {
        return 'Masukkan alamat email yang valid.';
    }

    if (str_contains($lower, 'is_unique') || str_contains($lower, 'already used') || str_contains($lower, 'not unique')) {
        return 'Email ini sudah terdaftar.';
    }

    if (str_contains($lower, 'matches')) {
        return 'Konfirmasi password harus sama dengan password.';
    }

    if (str_contains($lower, 'min_length')) {
        return $field === 'password' ? 'Password minimal 8 karakter.' : $label . ' terlalu pendek.';
    }

    if (str_contains($lower, 'max_length')) {
        return $label . ' terlalu panjang.';
    }

    return strtr($text, [
        'The Name field is required.' => 'Nama lengkap wajib diisi.',
        'The Email field is required.' => 'Email wajib diisi.',
        'The Password field is required.' => 'Password wajib diisi.',
        'The Password Confirm field is required.' => 'Konfirmasi password wajib diisi.',
        'The Email field must contain a valid email address.' => 'Masukkan alamat email yang valid.',
        'The Password Confirm field does not match the Password field.' => 'Konfirmasi password harus sama dengan password.',
    ]);
};
$aaAuthFieldError = static function (string $field) use ($aaAuthErrors, $aaAuthTranslateError): string {
    return isset($aaAuthErrors[$field]) ? $aaAuthTranslateError($aaAuthErrors[$field], $field) : '';
};
$aaAuthGeneralErrors = array_filter(
    $aaAuthErrors,
    static fn ($value, $key): bool => ! is_string($key) || ! in_array($key, ['name', 'email', 'password', 'password_confirm'], true),
    ARRAY_FILTER_USE_BOTH
);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Daftar - Ada Acara') ?></title>
    <?= view('components/noindex_meta') ?>
    <link rel="icon" type="image/png" href="<?= aa_asset_url('assets/img/logo2.png') ?>">
    <?= view('components/app_ui_assets') ?>
    <style>
        :root {
            --aa-auth-ink: #33284a;
            --aa-auth-muted: #7d758a;
            --aa-auth-purple: #8f65df;
            --aa-auth-purple-dark: #7550c4;
            --aa-auth-line: #eee7ef;
            --aa-auth-panel: #fff9f5;
            --aa-auth-card: rgba(255, 255, 255, .92);
            --aa-auth-shadow: 0 24px 70px rgba(91, 67, 118, .18);
        }

        * {
            box-sizing: border-box;
        }

        body.aa-auth-ui {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 12%, rgba(151, 112, 225, .20), transparent 30%),
                radial-gradient(circle at 88% 16%, rgba(255, 209, 218, .55), transparent 34%),
                linear-gradient(135deg, #f5edf9 0%, #fff8f2 48%, #f0e9fb 100%);
            color: var(--aa-auth-ink);
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body.aa-auth-ui a {
            color: inherit;
        }

        body.aa-auth-ui button,
        body.aa-auth-ui input {
            font: inherit;
        }

        .aa-auth-page {
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 42px 22px;
        }

        .aa-auth-shell {
            position: relative;
            isolation: isolate;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(360px, 430px);
            width: min(1120px, 100%);
            min-height: 690px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .72);
            border-radius: 34px;
            background: rgba(255, 246, 240, .72);
            box-shadow: var(--aa-auth-shadow), inset 1px 1px 0 rgba(255, 255, 255, .82);
        }

        .aa-auth-shell::before,
        .aa-auth-shell::after {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        .aa-auth-shell::before {
            background: url("<?= aa_asset_url('assets/img/auth-illustration.png') ?>") 40% center / cover no-repeat;
        }

        .aa-auth-shell::after {
            background:
                linear-gradient(90deg, rgba(255, 246, 240, .06) 0%, rgba(255, 246, 240, .12) 42%, rgba(255, 246, 240, .68) 65%, rgba(255, 246, 240, .9) 100%),
                radial-gradient(circle at 78% 14%, rgba(255, 255, 255, .48), transparent 26%);
        }

        .aa-auth-visual {
            position: relative;
            z-index: 1;
            min-height: 690px;
            overflow: visible;
            background: transparent;
        }

        .aa-auth-visual::after {
            content: none;
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(255, 246, 240, .02), rgba(255, 246, 240, .62));
            pointer-events: none;
        }

        .aa-auth-illustration {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: 43% center;
            opacity: 0;
            visibility: hidden;
        }

        .aa-auth-brand {
            position: absolute;
            z-index: 2;
            top: 32px;
            left: 38px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .aa-auth-brand img {
            width: 116px;
            height: auto;
            display: block;
            filter: invert(20%) sepia(18%) saturate(1230%) hue-rotate(227deg) brightness(92%) contrast(92%) drop-shadow(0 4px 10px rgba(71, 54, 90, .08));
        }

        .aa-auth-hero-copy {
            position: absolute;
            z-index: 2;
            top: 145px;
            left: 58px;
            max-width: 330px;
        }

        .aa-auth-hero-copy h1 {
            margin: 0 0 12px;
            color: #48345f;
            font-size: 32px;
            line-height: 1.08;
            letter-spacing: 0;
            font-weight: 900;
        }

        .aa-auth-hero-copy p {
            margin: 0;
            color: #675b76;
            font-size: 15px;
            line-height: 1.55;
            font-weight: 700;
        }

        .aa-auth-main {
            position: relative;
            z-index: 1;
            display: grid;
            min-height: 690px;
            place-items: center;
            padding: 34px;
            background: transparent;
        }

        .aa-auth-card {
            position: relative;
            width: min(390px, 100%);
            overflow: hidden;
            border: 1px solid rgba(111, 83, 139, .10);
            border-radius: 30px;
            background: var(--aa-auth-card);
            padding: 38px 38px 28px;
            box-shadow: 0 22px 58px rgba(89, 68, 112, .16), inset 1px 1px 0 rgba(255, 255, 255, .9);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .aa-auth-card > * {
            position: relative;
            z-index: 1;
        }

        .aa-auth-card::before {
            content: none;
        }

        .aa-mobile-logo {
            display: none;
            width: fit-content;
            align-items: center;
            justify-content: center;
            margin: 0 auto 22px;
        }

        .aa-mobile-logo img {
            width: 126px;
            height: auto;
            filter: invert(20%) sepia(18%) saturate(1230%) hue-rotate(227deg) brightness(92%) contrast(92%);
        }

        .aa-auth-title {
            margin: 0;
            color: #3f3159;
            font-size: 25px;
            line-height: 1.15;
            text-align: center;
            font-weight: 900;
            letter-spacing: 0;
        }

        .aa-auth-subtitle {
            margin: 9px auto 24px;
            max-width: 290px;
            color: var(--aa-auth-muted);
            font-size: 13px;
            line-height: 1.55;
            text-align: center;
            font-weight: 700;
        }

        .aa-form {
            display: grid;
            gap: 14px;
        }

        .aa-label {
            display: block;
            margin: 0 0 7px;
            color: #4c4260;
            font-size: 12px;
            font-weight: 850;
        }

        .aa-field-error {
            margin: 7px 0 0;
            color: #be123c;
            font-size: 11px;
            line-height: 1.45;
            font-weight: 800;
        }

        .aa-field-error:empty {
            display: none;
        }

        .aa-input-wrap {
            position: relative;
        }

        .aa-input-icon {
            position: absolute;
            top: 50%;
            left: 13px;
            display: grid;
            width: 28px;
            height: 28px;
            place-items: center;
            transform: translateY(-50%);
            border-radius: 9px;
            background: #f1e9ff;
            color: var(--aa-auth-purple);
            pointer-events: none;
        }

        .aa-input-icon svg,
        .aa-icon {
            width: 18px;
            height: 18px;
            display: block;
        }

        .aa-input {
            width: 100%;
            min-height: 48px;
            border: 1px solid var(--aa-auth-line);
            border-radius: 12px;
            background: rgba(255, 255, 255, .86);
            color: var(--aa-auth-ink);
            outline: 0;
            padding: 0 46px 0 52px;
            font-size: var(--aa-auth-input-size, 13px);
            font-weight: 700;
            line-height: 1.2;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7);
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .aa-input[type="email"],
        .aa-input[name="name"] {
            padding-right: 16px;
        }

        .aa-input::placeholder {
            color: #b5adbe;
        }

        .aa-input:focus {
            border-color: rgba(143, 101, 223, .55);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(143, 101, 223, .12);
        }

        .aa-password-toggle {
            position: absolute;
            top: 50%;
            right: 11px;
            display: grid;
            width: 32px;
            height: 32px;
            place-items: center;
            transform: translateY(-50%);
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #9b92a8;
            cursor: pointer;
        }

        .aa-password-toggle:hover {
            background: #f5effc;
            color: var(--aa-auth-purple-dark);
        }

        .aa-terms {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            color: #756b83;
            font-size: 12px;
            line-height: 1.5;
            font-weight: 700;
        }

        .aa-terms input {
            width: 16px;
            height: 16px;
            margin: 1px 0 0;
            accent-color: var(--aa-auth-purple);
            flex: 0 0 auto;
        }

        .aa-terms a,
        .aa-login-copy a {
            color: var(--aa-auth-purple-dark);
            font-weight: 900;
            text-decoration: none;
        }

        .aa-terms a:hover,
        .aa-login-copy a:hover {
            text-decoration: underline;
        }

        .aa-submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 48px;
            border: 0;
            border-radius: 13px;
            background: linear-gradient(135deg, #9c74e6, #7f55d8);
            color: #fff;
            cursor: pointer;
            font-size: 14px;
            font-weight: 900;
            box-shadow: 0 15px 30px rgba(127, 85, 216, .28);
            transition: transform .16s ease, box-shadow .16s ease, opacity .16s ease;
        }

        .aa-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 20px 38px rgba(127, 85, 216, .34);
        }

        .aa-submit-btn:disabled {
            opacity: .72;
            cursor: wait;
            transform: none;
        }

        .aa-loading-dot {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            border: 2px solid rgba(255, 255, 255, .45);
            border-top-color: #fff;
            border-radius: 999px;
            animation: aaAuthSpin .8s linear infinite;
        }

        @keyframes aaAuthSpin {
            to {
                transform: rotate(360deg);
            }
        }

        .aa-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
            color: #9b92a8;
            font-size: 12px;
            font-weight: 800;
            text-align: center;
        }

        .aa-divider::before,
        .aa-divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: var(--aa-auth-line);
        }

        .aa-social-row {
            display: grid;
            gap: 10px;
        }

        .aa-social-btn {
            display: inline-flex;
            min-height: 45px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            border: 1px solid var(--aa-auth-line);
            border-radius: 12px;
            background: rgba(255, 255, 255, .82);
            color: #51465f;
            cursor: pointer;
            font-size: 13px;
            font-weight: 850;
            text-decoration: none;
            transition: transform .16s ease, border-color .16s ease, background .16s ease;
        }

        .aa-social-btn:hover {
            transform: translateY(-1px);
            border-color: rgba(143, 101, 223, .28);
            background: #fff;
        }

        .aa-social-btn.is-muted {
            color: #6d6578;
        }

        .aa-login-copy {
            margin: 18px 0 0;
            color: #8a8196;
            font-size: 12px;
            text-align: center;
            font-weight: 800;
        }

        .aa-alert {
            margin: 0 0 16px;
            border: 1px solid rgba(248, 113, 113, .36);
            border-radius: 14px;
            background: rgba(254, 226, 226, .72);
            color: #b42318;
            padding: 12px 14px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.45;
        }

        .aa-alert.is-success {
            border-color: rgba(45, 212, 191, .35);
            background: rgba(204, 251, 241, .72);
            color: #0f766e;
        }

        .aa-alert ul {
            margin: 0;
            padding-left: 18px;
        }

        .aa-auth-brand,
        .aa-auth-hero-copy > *,
        .aa-auth-card > *,
        .aa-form > *,
        .aa-social-row > * {
            animation: aaAuthFadeUp .64s cubic-bezier(.22, .8, .26, 1) both;
        }

        .aa-auth-brand {
            animation-delay: .04s;
        }

        .aa-auth-hero-copy > *:nth-child(1),
        .aa-mobile-logo {
            animation-delay: .10s;
        }

        .aa-auth-hero-copy > *:nth-child(2),
        .aa-auth-title {
            animation-delay: .16s;
        }

        .aa-auth-subtitle {
            animation-delay: .22s;
        }

        .aa-alert {
            animation-delay: .26s;
        }

        .aa-form > *:nth-child(1) {
            animation-delay: .28s;
        }

        .aa-form > *:nth-child(2) {
            animation-delay: .34s;
        }

        .aa-form > *:nth-child(3) {
            animation-delay: .40s;
        }

        .aa-form > *:nth-child(4) {
            animation-delay: .46s;
        }

        .aa-form > *:nth-child(5) {
            animation-delay: .52s;
        }

        .aa-form > *:nth-child(6) {
            animation-delay: .58s;
        }

        .aa-divider {
            animation-delay: .62s;
        }

        .aa-social-row > *:nth-child(1) {
            animation-delay: .68s;
        }

        .aa-social-row > *:nth-child(2),
        .aa-login-copy {
            animation-delay: .74s;
        }

        @keyframes aaAuthFadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 900px) {
            .aa-auth-page {
                padding: 22px;
            }

            .aa-auth-shell {
                grid-template-columns: 1fr;
                width: min(520px, 100%);
                min-height: 0;
                background: rgba(255, 246, 240, .72);
            }

            .aa-auth-shell::before,
            .aa-auth-shell::after {
                content: none;
            }

            .aa-auth-visual {
                display: none;
            }

            .aa-auth-main {
                min-height: 0;
                padding: 24px;
            }

            .aa-auth-card {
                padding: 34px 28px 28px;
            }

            .aa-auth-card::before {
                content: "";
                position: absolute;
                inset: 0 0 auto;
                height: 230px;
                background:
                    linear-gradient(180deg, rgba(255, 249, 245, .18) 0%, rgba(255, 249, 245, .76) 64%, rgba(255, 255, 255, .96) 100%),
                    url("<?= aa_asset_url('assets/img/auth-illustration.png') ?>");
                background-size: cover;
                background-position: 42% 44%;
                opacity: .46;
                -webkit-mask-image: linear-gradient(180deg, #000 0%, #000 70%, transparent 100%);
                mask-image: linear-gradient(180deg, #000 0%, #000 70%, transparent 100%);
                pointer-events: none;
            }

            .aa-mobile-logo {
                display: inline-flex;
            }
        }

        @media (max-width: 520px) {
            .aa-auth-page {
                align-items: stretch;
                padding: 0;
            }

            .aa-auth-shell {
                min-height: 100vh;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                background: transparent;
            }

            .aa-auth-main {
                padding: 22px;
            }

            .aa-auth-card {
                border-radius: 26px;
                padding: 30px 22px 24px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .aa-auth-brand,
            .aa-auth-hero-copy > *,
            .aa-auth-card > *,
            .aa-form > *,
            .aa-social-row > * {
                animation: none;
            }
        }
    </style>
    <?= view('components/modern_alerts') ?>
</head>
<body class="aa-app-ui aa-auth-ui">
    <main class="aa-auth-page">
        <section class="aa-auth-shell" aria-label="Daftar AdaAcara">
            <div class="aa-auth-visual">
                <img class="aa-auth-illustration" src="<?= aa_asset_url('assets/img/auth-illustration.png') ?>" alt="" loading="eager" decoding="async">
                <a class="aa-auth-brand" href="<?= site_url('/') ?>" aria-label="AdaAcara">
                    <img src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                </a>
                <div class="aa-auth-hero-copy">
                    <h1>Welcome to AdaAcara.</h1>
                    <p>Buat akun, pilih template, lalu mulai susun undangan website yang siap dibagikan.</p>
                </div>
            </div>

            <div class="aa-auth-main">
                <div class="aa-auth-card">
                    <a href="<?= site_url('/') ?>" class="aa-mobile-logo" aria-label="AdaAcara">
                        <img src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                    </a>

                    <h1 class="aa-auth-title">Buat akun AdaAcara</h1>
                    <p class="aa-auth-subtitle">Mulai gratis dan simpan semua desain undangan di dashboard kamu.</p>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="aa-alert is-success"><?= esc(session()->getFlashdata('success')) ?></div>
                    <?php endif ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="aa-alert"><?= esc(session()->getFlashdata('error')) ?></div>
                    <?php endif ?>

                    <?php if ($aaAuthGeneralErrors !== []): ?>
                        <div class="aa-alert">
                            <ul>
                                <?php foreach ($aaAuthGeneralErrors as $error): ?>
                                    <li><?= esc($aaAuthTranslateError($error)) ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endif ?>

                    <form action="<?= site_url('register') ?>" method="post" class="aa-form" novalidate>
                        <?= csrf_field() ?>

                        <div>
                            <label class="aa-label" for="name">Nama lengkap</label>
                            <div class="aa-input-wrap">
                                <span class="aa-input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M4.5 20a7.5 7.5 0 0 1 15 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                <input class="aa-input" id="name" name="name" type="text" value="<?= esc(old('name')) ?>" placeholder="Masukkan nama lengkap" autocomplete="name" required>
                            </div>
                            <p class="aa-field-error"><?= esc($aaAuthFieldError('name')) ?></p>
                        </div>

                        <div>
                            <label class="aa-label" for="email">Alamat email</label>
                            <div class="aa-input-wrap">
                                <span class="aa-input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M4 7.5h16v10H4v-10Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <path d="m5 8 7 5 7-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <input class="aa-input" id="email" name="email" type="email" value="<?= esc(old('email')) ?>" placeholder="Masukkan email" autocomplete="email" required>
                            </div>
                            <p class="aa-field-error"><?= esc($aaAuthFieldError('email')) ?></p>
                        </div>

                        <div>
                            <label class="aa-label" for="password">Password</label>
                            <div class="aa-input-wrap">
                                <span class="aa-input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M5.5 10h13v10h-13V10Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <input class="aa-input" id="password" name="password" type="password" placeholder="Buat password" autocomplete="new-password" required>
                                <button class="aa-password-toggle" type="button" aria-label="Tampilkan password" aria-pressed="false" data-password-toggle="password">
                                    <svg class="aa-icon aa-eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M3 12s3.3-5 9-5 9 5 9 5-3.3 5-9 5-9-5-9-5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <path d="M12 14.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                    <svg class="aa-icon aa-eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true" style="display:none">
                                        <path d="m4 4 16 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M9.8 6.4A9.5 9.5 0 0 1 12 6c5.7 0 9 6 9 6a15 15 0 0 1-2.3 3.1M14.1 14.4A2.5 2.5 0 0 1 9.6 9.9M6.7 8A14.7 14.7 0 0 0 3 12s3.3 6 9 6c1.2 0 2.3-.3 3.3-.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="aa-field-error"><?= esc($aaAuthFieldError('password')) ?></p>
                        </div>

                        <div>
                            <label class="aa-label" for="password_confirm">Konfirmasi password</label>
                            <div class="aa-input-wrap">
                                <span class="aa-input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M5.5 10h13v10h-13V10Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <path d="m9 15 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <input class="aa-input" id="password_confirm" name="password_confirm" type="password" placeholder="Ulangi password" autocomplete="new-password" required>
                                <button class="aa-password-toggle" type="button" aria-label="Tampilkan konfirmasi password" aria-pressed="false" data-password-toggle="password_confirm">
                                    <svg class="aa-icon aa-eye-open" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M3 12s3.3-5 9-5 9 5 9 5-3.3 5-9 5-9-5-9-5Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <path d="M12 14.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                    <svg class="aa-icon aa-eye-closed" viewBox="0 0 24 24" fill="none" aria-hidden="true" style="display:none">
                                        <path d="m4 4 16 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M9.8 6.4A9.5 9.5 0 0 1 12 6c5.7 0 9 6 9 6a15 15 0 0 1-2.3 3.1M14.1 14.4A2.5 2.5 0 0 1 9.6 9.9M6.7 8A14.7 14.7 0 0 0 3 12s3.3 6 9 6c1.2 0 2.3-.3 3.3-.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="aa-field-error"><?= esc($aaAuthFieldError('password_confirm')) ?></p>
                        </div>

                        <label class="aa-terms">
                            <input type="checkbox" name="terms" value="1" required>
                            <span>Saya setuju dengan <a href="<?= site_url('terms') ?>">Syarat & Ketentuan</a> dan <a href="<?= site_url('privacy') ?>">Kebijakan Privasi</a>.</span>
                        </label>

                        <button class="aa-submit-btn" type="submit">Daftar</button>
                    </form>

                    <div class="aa-divider">atau</div>

                    <div class="aa-social-row">
                        <a class="aa-social-btn" href="<?= site_url('auth/google') ?>">
                            <svg class="aa-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="#4285F4" d="M21.6 12.2c0-.7-.1-1.3-.2-1.9H12v3.6h5.4a4.6 4.6 0 0 1-2 3v2.5h3.2c1.9-1.8 3-4.4 3-7.2Z"/>
                                <path fill="#34A853" d="M12 22c2.7 0 5-.9 6.6-2.6L15.4 17c-.9.6-2 .9-3.4.9-2.6 0-4.8-1.8-5.6-4.1H3.1v2.5A10 10 0 0 0 12 22Z"/>
                                <path fill="#FBBC05" d="M6.4 13.8a6 6 0 0 1 0-3.6V7.7H3.1a10 10 0 0 0 0 8.6l3.3-2.5Z"/>
                                <path fill="#EA4335" d="M12 6.1c1.5 0 2.8.5 3.8 1.5l2.9-2.9A9.7 9.7 0 0 0 12 2a10 10 0 0 0-8.9 5.7l3.3 2.5c.8-2.3 3-4.1 5.6-4.1Z"/>
                            </svg>
                            Lanjutkan dengan Google
                        </a>
                    </div>

                    <p class="aa-login-copy">Sudah punya akun? <a href="<?= site_url('login') ?>">Masuk</a></p>
                </div>
            </div>
        </section>
    </main>

    <script>
        (function () {
            document.querySelectorAll('[data-password-toggle]').forEach(function (toggleButton) {
                const targetId = toggleButton.getAttribute('data-password-toggle');
                const passwordInput = targetId ? document.getElementById(targetId) : null;
                if (!passwordInput) {
                    return;
                }

                toggleButton.addEventListener('click', function () {
                    const shouldShow = passwordInput.type === 'password';
                    passwordInput.type = shouldShow ? 'text' : 'password';
                    toggleButton.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
                    toggleButton.setAttribute('aria-label', shouldShow ? 'Sembunyikan password' : 'Tampilkan password');
                    const openIcon = toggleButton.querySelector('.aa-eye-open');
                    const closedIcon = toggleButton.querySelector('.aa-eye-closed');
                    if (openIcon && closedIcon) {
                        openIcon.style.display = shouldShow ? 'none' : '';
                        closedIcon.style.display = shouldShow ? '' : 'none';
                    }
                });
            });

            const fitInputs = Array.from(document.querySelectorAll('.aa-input[type="email"], .aa-input[name="name"]'));
            if (fitInputs.length) {
                const measurer = document.createElement('span');
                measurer.setAttribute('aria-hidden', 'true');
                measurer.style.cssText = 'position:fixed;left:-9999px;top:-9999px;visibility:hidden;white-space:pre;pointer-events:none;';
                document.body.appendChild(measurer);

                function fitInput(input) {
                    const styles = window.getComputedStyle(input);
                    const baseSize = parseFloat(input.dataset.aaBaseFontSize || styles.fontSize) || 13;
                    const minSize = 10.8;
                    const padding = (parseFloat(styles.paddingLeft) || 0) + (parseFloat(styles.paddingRight) || 0);
                    const availableWidth = Math.max(40, input.clientWidth - padding - 4);
                    const text = input.value || input.placeholder || '';
                    let nextSize = baseSize;

                    input.dataset.aaBaseFontSize = String(baseSize);
                    measurer.style.fontFamily = styles.fontFamily;
                    measurer.style.fontWeight = styles.fontWeight;
                    measurer.style.letterSpacing = styles.letterSpacing;
                    measurer.textContent = text;

                    while (nextSize > minSize) {
                        measurer.style.fontSize = nextSize + 'px';
                        if (measurer.offsetWidth <= availableWidth) {
                            break;
                        }
                        nextSize -= .5;
                    }

                    input.style.setProperty('--aa-auth-input-size', nextSize.toFixed(1) + 'px');
                }

                const refitAll = function () {
                    fitInputs.forEach(fitInput);
                };

                fitInputs.forEach(function (input) {
                    input.addEventListener('input', function () {
                        fitInput(input);
                    });
                    input.addEventListener('change', function () {
                        fitInput(input);
                    });
                    fitInput(input);
                });

                window.addEventListener('resize', refitAll, { passive: true });
                window.requestAnimationFrame(refitAll);
            }

            const registerForm = document.querySelector('form[action="<?= site_url('register') ?>"]');
            const registerButton = registerForm ? registerForm.querySelector('.aa-submit-btn[type="submit"]') : null;
            if (registerForm && registerButton) {
                registerForm.addEventListener('submit', function () {
                    if (!registerButton.dataset.originalHtml) {
                        registerButton.dataset.originalHtml = registerButton.innerHTML;
                    }
                    registerButton.disabled = true;
                    registerButton.classList.add('is-loading');
                    registerButton.innerHTML = '<span class="aa-loading-dot" aria-hidden="true"></span>Memproses...';
                });
            }
        })();
    </script>
</body>
</html>
