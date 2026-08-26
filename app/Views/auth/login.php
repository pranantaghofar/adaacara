<?php
$aaAuthErrors = session()->getFlashdata('errors');
$aaAuthErrors = is_array($aaAuthErrors) ? $aaAuthErrors : [];
$aaAuthFieldLabels = [
    'email' => 'Email',
    'password' => 'Password',
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

    return strtr($text, [
        'The Email field is required.' => 'Email wajib diisi.',
        'The Password field is required.' => 'Password wajib diisi.',
        'The Email field must contain a valid email address.' => 'Masukkan alamat email yang valid.',
    ]);
};
$aaAuthFieldError = static function (string $field) use ($aaAuthErrors, $aaAuthTranslateError): string {
    return isset($aaAuthErrors[$field]) ? $aaAuthTranslateError($aaAuthErrors[$field], $field) : '';
};
$aaAuthGeneralErrors = array_filter(
    $aaAuthErrors,
    static fn ($value, $key): bool => ! is_string($key) || ! in_array($key, ['email', 'password'], true),
    ARRAY_FILTER_USE_BOTH
);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Login - Ada Acara') ?></title>
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
            padding: 42px 38px 30px;
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
            display: block;
            filter: invert(20%) sepia(18%) saturate(1230%) hue-rotate(227deg) brightness(92%) contrast(92%);
        }

        .aa-auth-title {
            margin: 0;
            color: #3f3156;
            text-align: center;
            font-size: 27px;
            line-height: 1.15;
            letter-spacing: 0;
            font-weight: 900;
        }

        .aa-auth-subtitle {
            margin: 8px 0 24px;
            color: var(--aa-auth-muted);
            text-align: center;
            font-size: 13px;
            line-height: 1.5;
            font-weight: 700;
        }

        .aa-alert {
            margin-bottom: 15px;
            border: 1px solid rgba(225, 29, 72, .16);
            border-radius: 16px;
            background: #fff1f2;
            color: #9f1239;
            padding: 12px 13px;
            font-size: 12px;
            line-height: 1.5;
            font-weight: 750;
        }

        .aa-alert.is-success {
            border-color: rgba(127, 85, 216, .18);
            background: #f6f0ff;
            color: #6f43c3;
        }

        .aa-alert ul {
            margin: 0;
            padding-left: 17px;
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

        .aa-form {
            display: grid;
            gap: 14px;
        }

        .aa-field-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 7px;
        }

        .aa-label,
        .aa-forgot {
            color: #574765;
            font-size: 12px;
            font-weight: 850;
        }

        .aa-forgot {
            color: var(--aa-auth-purple-dark);
            text-decoration: none;
        }

        .aa-input-wrap {
            position: relative;
        }

        .aa-input-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            display: grid;
            width: 27px;
            height: 27px;
            place-items: center;
            border-radius: 9px;
            background: #f0e8ff;
            color: var(--aa-auth-purple);
            transform: translateY(-50%);
            pointer-events: none;
        }

        .aa-input-icon svg {
            width: 16px;
            height: 16px;
        }

        .aa-input {
            width: 100%;
            min-height: 46px;
            border: 1px solid var(--aa-auth-line);
            border-radius: 13px;
            background: rgba(255, 255, 255, .82);
            color: #32263f;
            font-size: var(--aa-auth-input-size, 13px);
            font-weight: 700;
            line-height: 1.2;
            outline: 0;
            padding: 0 48px 0 50px;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7);
            transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .aa-input[type="email"],
        .aa-input[name="name"] {
            padding-right: 16px;
        }

        .aa-input:focus {
            border-color: rgba(143, 101, 223, .6);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(143, 101, 223, .12);
        }

        .aa-input::placeholder {
            color: #9d96a7;
        }

        .aa-password-toggle {
            position: absolute;
            right: 7px;
            top: 50%;
            display: grid;
            width: 36px;
            height: 36px;
            place-items: center;
            border: 0;
            border-radius: 12px;
            background: transparent;
            color: #9b93a5;
            cursor: pointer;
            transform: translateY(-50%);
        }

        .aa-password-toggle:hover {
            background: rgba(143, 101, 223, .10);
            color: var(--aa-auth-purple-dark);
        }

        .aa-login-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: -2px;
        }

        .aa-remember {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #746b80;
            font-size: 12px;
            font-weight: 750;
            cursor: pointer;
        }

        .aa-remember input {
            width: 17px;
            height: 17px;
            accent-color: var(--aa-auth-purple);
        }

        .aa-social-row {
            display: grid;
            gap: 10px;
        }

        .aa-social-btn,
        .aa-submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            min-height: 45px;
            border-radius: 13px;
            border: 1px solid var(--aa-auth-line);
            background: #fff;
            color: #574765;
            cursor: pointer;
            font-size: 13px;
            font-weight: 850;
            text-decoration: none;
            transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .aa-social-btn:hover {
            transform: translateY(-1px);
            border-color: rgba(143, 101, 223, .28);
            box-shadow: 0 12px 28px rgba(89, 68, 112, .10);
        }

        .aa-social-btn.is-muted {
            color: #6c6178;
        }

        .aa-submit-btn {
            min-height: 49px;
            border: 0;
            background: linear-gradient(135deg, #a878f1 0%, #8158d8 100%);
            color: #fff;
            box-shadow: 0 14px 30px rgba(129, 88, 216, .28);
        }

        .aa-submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(129, 88, 216, .34);
        }

        .aa-submit-btn.is-loading {
            cursor: wait;
            opacity: .78;
            pointer-events: none;
        }

        .aa-loading-dot {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid currentColor;
            border-top-color: transparent;
            border-radius: 999px;
            animation: aaSpin .75s linear infinite;
        }

        .aa-divider {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            gap: 13px;
            margin: 18px 0 13px;
            color: #8d8498;
            font-size: 12px;
            font-weight: 750;
        }

        .aa-divider::before,
        .aa-divider::after {
            content: "";
            height: 1px;
            background: var(--aa-auth-line);
        }

        .aa-register-copy {
            margin: 18px 0 0;
            text-align: center;
            color: #7f758b;
            font-size: 12px;
            font-weight: 750;
        }

        .aa-register-copy a {
            color: var(--aa-auth-purple-dark);
            font-weight: 900;
            text-decoration: none;
        }

        .aa-icon {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
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

        .aa-divider {
            animation-delay: .50s;
        }

        .aa-social-row > *:nth-child(1) {
            animation-delay: .56s;
        }

        .aa-social-row > *:nth-child(2),
        .aa-register-copy {
            animation-delay: .62s;
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

        @keyframes aaSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 980px) {
            .aa-auth-page {
                padding: 20px;
            }

            .aa-auth-shell {
                grid-template-columns: 1fr;
                width: min(520px, 100%);
                min-height: auto;
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
                min-height: 100vh;
                padding: 32px 18px;
            }

            .aa-auth-card {
                width: min(410px, 100%);
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
                display: flex;
            }
        }

        @media (max-width: 520px) {
            .aa-auth-page {
                padding: 0;
            }

            .aa-auth-shell {
                min-height: 100vh;
                border: 0;
                border-radius: 0;
            }

            .aa-auth-main {
                padding: 24px 16px;
            }

            .aa-auth-card {
                border-radius: 26px;
                padding: 32px 22px 24px;
            }

            .aa-auth-title {
                font-size: 25px;
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
</head>
<body class="aa-app-ui aa-auth-ui">
    <?= view('components/modern_alerts') ?>

    <main class="aa-auth-page">
        <section class="aa-auth-shell" aria-label="Masuk AdaAcara">
            <div class="aa-auth-visual">
                <img class="aa-auth-illustration" src="<?= aa_asset_url('assets/img/auth-illustration.png') ?>" alt="" loading="eager" decoding="async">
                <a class="aa-auth-brand" href="<?= site_url('/') ?>" aria-label="AdaAcara">
                    <img src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                </a>
                <div class="aa-auth-hero-copy">
                    <h1>Selamat datang kembali.</h1>
                    <p>Lanjutkan desain undangan, kelola RSVP, dan publish link acara dari studio AdaAcara.</p>
                </div>
            </div>

            <div class="aa-auth-main">
                <div class="aa-auth-card">
                    <a href="<?= site_url('/') ?>" class="aa-mobile-logo" aria-label="AdaAcara">
                        <img src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="eager">
                    </a>

                    <h1 class="aa-auth-title">Masuk ke akun kamu</h1>
                    <p class="aa-auth-subtitle">Buka dashboard, editor, dan semua undangan yang sudah kamu buat.</p>

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

                    <form action="<?= site_url('login') ?>" method="post" class="aa-form" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="redirect" value="<?= esc(old('redirect', $redirect ?? ''), 'attr') ?>">

                        <div>
                            <div class="aa-field-head">
                                <label class="aa-label" for="email">Alamat email</label>
                            </div>
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
                            <div class="aa-field-head">
                                <label class="aa-label" for="password">Password</label>
                                <a class="aa-forgot" href="<?= site_url('forgot-password') ?>">Lupa password?</a>
                            </div>
                            <div class="aa-input-wrap">
                                <span class="aa-input-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path d="M7 10V8a5 5 0 0 1 10 0v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        <path d="M5.5 10h13v10h-13V10Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <input class="aa-input" id="password" name="password" type="password" placeholder="Masukkan password" autocomplete="current-password" required>
                                <button class="aa-password-toggle" type="button" aria-label="Tampilkan password" aria-pressed="false" data-password-toggle>
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

                        <div class="aa-login-options">
                            <label class="aa-remember">
                                <input type="checkbox" name="remember" value="1" checked>
                                Ingat saya
                            </label>
                        </div>

                        <button class="aa-submit-btn" type="submit">Masuk</button>
                    </form>

                    <div class="aa-divider">atau</div>

                    <div class="aa-social-row">
                        <a class="aa-social-btn" href="<?= site_url('auth/google' . (! empty($redirect ?? '') ? '?redirect=' . rawurlencode($redirect) : '')) ?>">
                            <svg class="aa-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="#4285F4" d="M21.6 12.2c0-.7-.1-1.3-.2-1.9H12v3.6h5.4a4.6 4.6 0 0 1-2 3v2.5h3.2c1.9-1.8 3-4.4 3-7.2Z"/>
                                <path fill="#34A853" d="M12 22c2.7 0 5-.9 6.6-2.6L15.4 17c-.9.6-2 .9-3.4.9-2.6 0-4.8-1.8-5.6-4.1H3.1v2.5A10 10 0 0 0 12 22Z"/>
                                <path fill="#FBBC05" d="M6.4 13.8a6 6 0 0 1 0-3.6V7.7H3.1a10 10 0 0 0 0 8.6l3.3-2.5Z"/>
                                <path fill="#EA4335" d="M12 6.1c1.5 0 2.8.5 3.8 1.5l2.9-2.9A9.7 9.7 0 0 0 12 2a10 10 0 0 0-8.9 5.7l3.3 2.5c.8-2.3 3-4.1 5.6-4.1Z"/>
                            </svg>
                            Lanjutkan dengan Google
                        </a>
                    </div>

                    <p class="aa-register-copy">Belum punya akun? <a href="<?= site_url('register') ?>">Daftar</a></p>
                </div>
            </div>
        </section>
    </main>

    <script>
        (function () {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('[data-password-toggle]');
            if (passwordInput && toggleButton) {
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
            }

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

            const loginForm = document.querySelector('form[action="<?= site_url('login') ?>"]');
            const loginButton = loginForm ? loginForm.querySelector('.aa-submit-btn[type="submit"]') : null;
            if (loginForm && loginButton) {
                loginForm.addEventListener('submit', function () {
                    if (!loginButton.dataset.originalHtml) {
                        loginButton.dataset.originalHtml = loginButton.innerHTML;
                    }
                    loginButton.disabled = true;
                    loginButton.classList.add('is-loading');
                    loginButton.innerHTML = '<span class="aa-loading-dot" aria-hidden="true"></span>Memproses...';
                });
            }
        })();
    </script>
</body>
</html>
