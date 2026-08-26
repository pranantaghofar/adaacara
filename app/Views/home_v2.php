<?php
    $isLoggedIn = (bool) ($isLoggedIn ?? false);
    $loginUrl = $isLoggedIn ? site_url('dashboard') : site_url('login');
    $startUrl = $isLoggedIn ? site_url('templates') : site_url('register');

    $services = [
        ['Editor Visual', 'Drag drop', 'Multi halaman', 'Animasi'],
        ['Template Siap Pakai', 'Wedding', 'Aqiqah', 'Corporate'],
        ['Interaksi Tamu', 'RSVP', 'Guestbook', 'QR code'],
        ['Creator Market', 'Validasi', 'Komisi', 'Dashboard'],
    ];

    $projects = [
        ['Wedding Modern', 'Publish link /u/nikah-adam', 'cream'],
        ['Aqiqah Lembut', 'Musik, galeri, ucapan tamu', 'mint'],
        ['Seminar Premium', 'QR check-in dan RSVP', 'blue'],
        ['Birthday Playful', 'Countdown dan stiker', 'yellow'],
    ];

    $steps = [
        ['01', 'Brief acara', 'Tulis detail utama acara.'],
        ['02', 'Pilih visual', 'Mulai dari template atau canvas kosong.'],
        ['03', 'Edit bebas', 'Atur teks, foto, warna, musik, dan halaman.'],
        ['04', 'Publish link', 'Bagikan undangan sebagai website.'],
    ];

    $reviews = [
        ['Rapi untuk customer', 'Dashboard membuat order, RSVP, dan link undangan lebih mudah dipantau.', 'Seller'],
        ['Editornya terasa bebas', 'Saya bisa ubah layout, warna, musik, dan foto tanpa harus coding.', 'Creator'],
        ['Undangan langsung siap share', 'Preview mobile dan publish link membuat proses jadi singkat.', 'Customer'],
    ];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AdaAcara V2 - Webflow Style</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Prototype home-v2 AdaAcara dengan layout premium Webflow-style.">
    <link rel="icon" type="image/png" href="<?= esc(base_url('assets/img/logo2.png'), 'attr') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --aa-dark: #191923;
            --aa-ink: #252430;
            --aa-muted: #6d7080;
            --aa-line: #e7e2d8;
            --aa-card: #fbfaf7;
            --aa-white: #ffffff;
            --aa-cream: #f6f0e6;
            --aa-soft: #fffaf0;
            --aa-gold: #d7a237;
            --aa-lime: #dcecb4;
            --aa-lavender: #b8b4ff;
            --aa-violet: #7f65d7;
            --aa-blue: #c8e7ff;
            --aa-mint: #cdeee3;
            --aa-radius-xl: 34px;
            --aa-radius-lg: 26px;
            --aa-shadow: 0 24px 70px rgba(25, 25, 35, .11);
            --aa-inset: inset 1px 1px 0 rgba(255, 255, 255, .85), inset -1px -1px 0 rgba(25, 25, 35, .08);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: var(--aa-soft);
            color: var(--aa-ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            overflow-x: hidden;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button {
            font: inherit;
        }

        .aa-page {
            position: relative;
            min-height: 100vh;
            overflow: clip;
            background:
                radial-gradient(circle at 12% 12%, rgba(255, 205, 105, .22), transparent 34%),
                radial-gradient(circle at 85% 8%, rgba(184, 180, 255, .22), transparent 32%),
                linear-gradient(180deg, #fffaf0 0%, #f7f0e7 50%, #fffaf0 100%);
        }

        .aa-container {
            width: min(100% - 30px, 1320px);
            margin-inline: auto;
        }

        .aa-header-shell {
            position: fixed;
            z-index: 50;
            inset: 18px 0 auto;
            pointer-events: none;
        }

        .aa-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: 72px;
            border: 1px solid rgba(37, 36, 48, .1);
            border-radius: 999px;
            background: rgba(255, 255, 255, .74);
            padding: 10px 14px 10px 22px;
            box-shadow: 0 12px 40px rgba(25, 25, 35, .08);
            backdrop-filter: blur(18px);
            pointer-events: auto;
            transition: transform .28s ease, box-shadow .28s ease, background .28s ease;
        }

        .aa-header.is-scrolled {
            background: rgba(255, 255, 255, .9);
            box-shadow: 0 16px 44px rgba(25, 25, 35, .14);
            transform: translateY(-4px);
        }

        .aa-brand {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            min-width: 172px;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .aa-brand-mark {
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            border-radius: 50%;
            background: var(--aa-dark);
            color: var(--aa-white);
            box-shadow: var(--aa-inset);
        }

        .aa-brand-mark span {
            width: 18px;
            height: 18px;
            border: 3px solid currentColor;
            border-radius: 7px;
            transform: rotate(8deg);
        }

        .aa-nav {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #545667;
            font-size: 14px;
            font-weight: 600;
        }

        .aa-nav a {
            position: relative;
            display: inline-flex;
            min-height: 38px;
            align-items: center;
            border-radius: 999px;
            padding: 0 14px;
            transition: color .22s ease, background .22s ease, transform .22s ease;
        }

        .aa-nav a:hover,
        .aa-nav a.is-active {
            background: var(--aa-dark);
            color: var(--aa-white);
            transform: translateY(-1px);
        }

        .aa-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .aa-auth {
            color: #4c5062;
            font-size: 14px;
            font-weight: 700;
            padding: 0 12px;
        }

        .aa-button {
            position: relative;
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 1px solid rgba(25, 25, 35, .14);
            border-radius: 999px;
            background: var(--aa-dark);
            color: var(--aa-white);
            padding: 0 22px;
            font-size: 14px;
            font-weight: 800;
            box-shadow: var(--aa-inset), 0 18px 36px rgba(25, 25, 35, .18);
            transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
            overflow: hidden;
        }

        .aa-button:hover {
            background: #2a2938;
            transform: translateY(-2px);
            box-shadow: var(--aa-inset), 0 24px 46px rgba(25, 25, 35, .22);
        }

        .aa-button.is-light {
            background: var(--aa-white);
            color: var(--aa-dark);
            box-shadow: var(--aa-inset), 0 12px 28px rgba(25, 25, 35, .08);
        }

        .aa-button-arrow {
            display: grid;
            width: 26px;
            height: 26px;
            place-items: center;
            border-radius: 50%;
            background: var(--aa-gold);
            color: var(--aa-dark);
            transition: transform .25s ease;
        }

        .aa-button:hover .aa-button-arrow {
            transform: translateX(3px);
        }

        .aa-hero-vh {
            position: relative;
            height: 100vh;
            min-height: 820px;
        }

        .aa-hero {
            position: sticky;
            top: 0;
            display: grid;
            min-height: 100vh;
            place-items: center;
            overflow: clip;
            background:
                linear-gradient(135deg, rgba(255, 255, 255, .6), rgba(255, 246, 225, .42)),
                radial-gradient(circle at 16% 72%, rgba(215, 162, 55, .22), transparent 28%),
                radial-gradient(circle at 84% 68%, rgba(127, 101, 215, .18), transparent 30%);
        }

        .aa-hero-bg-grid {
            position: absolute;
            inset: 0;
            opacity: .32;
            background-image:
                linear-gradient(rgba(25, 25, 35, .05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(25, 25, 35, .05) 1px, transparent 1px);
            background-size: 72px 72px;
            mask-image: linear-gradient(to bottom, transparent, #000 20%, #000 80%, transparent);
        }

        .aa-hero-wrap {
            position: relative;
            z-index: 2;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 440px;
            align-items: center;
            gap: 58px;
            padding-top: 120px;
        }

        .aa-hero-copy {
            max-width: 860px;
        }

        .aa-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
            color: #4c4d5f;
            font-size: 15px;
            font-weight: 800;
        }

        .aa-eyebrow::before,
        .aa-subtitle-line::before,
        .aa-subtitle-line::after {
            content: "";
            display: inline-block;
            width: 76px;
            height: 1px;
            background: currentColor;
            opacity: .36;
        }

        .aa-eyebrow-dot {
            display: grid;
            width: 28px;
            height: 28px;
            place-items: center;
            border-radius: 50%;
            background: var(--aa-lavender);
            box-shadow: var(--aa-inset);
        }

        .aa-eyebrow-dot::after {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--aa-dark);
        }

        .aa-hero h1 {
            margin: 0;
            max-width: 890px;
            color: var(--aa-dark);
            font-size: clamp(64px, 8.6vw, 138px);
            line-height: .88;
            font-weight: 800;
            letter-spacing: -.07em;
        }

        .aa-hero h1 span {
            font-family: "Instrument Serif", serif;
            font-style: italic;
            font-weight: 400;
            letter-spacing: -.04em;
            background: linear-gradient(90deg, #2f2f82, #ad72ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .aa-hero-text {
            max-width: 590px;
            margin: 24px 0 0;
            color: var(--aa-muted);
            font-size: 18px;
            line-height: 1.72;
        }

        .aa-hero-cta {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 34px;
            flex-wrap: wrap;
        }

        .aa-hero-social {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 42px;
        }

        .aa-avatar-stack {
            display: flex;
            align-items: center;
            padding-left: 12px;
        }

        .aa-avatar {
            display: grid;
            width: 42px;
            height: 42px;
            place-items: center;
            border: 3px solid var(--aa-soft);
            border-radius: 50%;
            margin-left: -12px;
            background: var(--aa-dark);
            color: var(--aa-white);
            font-size: 12px;
            font-weight: 800;
        }

        .aa-avatar:nth-child(2) {
            background: var(--aa-gold);
            color: var(--aa-dark);
        }

        .aa-avatar:nth-child(3) {
            background: var(--aa-lavender);
            color: var(--aa-dark);
        }

        .aa-rating {
            color: #4f5261;
            font-size: 14px;
            font-weight: 700;
        }

        .aa-hero-art {
            position: relative;
            min-height: 560px;
        }

        .aa-studio-card {
            position: absolute;
            inset: 44px 12px auto auto;
            width: min(100%, 420px);
            border: 1px solid rgba(25, 25, 35, .12);
            border-radius: 32px;
            background: rgba(255, 255, 255, .7);
            box-shadow: var(--aa-shadow), var(--aa-inset);
            backdrop-filter: blur(18px);
            overflow: hidden;
            animation: aaFloat 7s ease-in-out infinite;
        }

        .aa-studio-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(25, 25, 35, .08);
            padding: 16px 18px;
        }

        .aa-window-dots {
            display: flex;
            gap: 7px;
        }

        .aa-window-dots span {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #dcd7cc;
        }

        .aa-window-dots span:nth-child(1) {
            background: #ff9c7e;
        }

        .aa-window-dots span:nth-child(2) {
            background: #ffd568;
        }

        .aa-window-dots span:nth-child(3) {
            background: #85d9b7;
        }

        .aa-publish-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 999px;
            background: var(--aa-dark);
            color: var(--aa-white);
            padding: 9px 12px;
            font-size: 12px;
            font-weight: 800;
        }

        .aa-publish-pill::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #7cffbd;
        }

        .aa-studio-body {
            display: grid;
            grid-template-columns: 62px 1fr 88px;
            min-height: 410px;
            background: linear-gradient(180deg, rgba(255, 250, 240, .8), rgba(255, 255, 255, .56));
        }

        .aa-tool-rail,
        .aa-props-rail {
            padding: 18px 12px;
        }

        .aa-tool-rail {
            display: grid;
            gap: 10px;
            align-content: start;
            border-right: 1px solid rgba(25, 25, 35, .08);
        }

        .aa-tool {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 14px;
            background: var(--aa-white);
            box-shadow: var(--aa-inset), 0 10px 22px rgba(25, 25, 35, .06);
            color: var(--aa-dark);
            font-size: 12px;
            font-weight: 900;
        }

        .aa-tool.is-active {
            background: var(--aa-gold);
        }

        .aa-canvas-wrap {
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .aa-invite-card {
            position: relative;
            display: flex;
            width: 190px;
            min-height: 315px;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            border: 1px solid rgba(25, 25, 35, .1);
            border-radius: 26px;
            background:
                radial-gradient(circle at 50% 20%, rgba(215, 162, 55, .2), transparent 24%),
                linear-gradient(180deg, #fffaf0, #ffffff);
            padding: 26px 18px 20px;
            text-align: center;
            box-shadow: 0 18px 44px rgba(25, 25, 35, .12);
            overflow: hidden;
        }

        .aa-invite-card::before,
        .aa-invite-card::after {
            content: "";
            position: absolute;
            border: 2px solid rgba(215, 162, 55, .45);
            border-radius: 999px 999px 40px 40px;
            pointer-events: none;
        }

        .aa-invite-card::before {
            inset: 16px 22px 78px;
        }

        .aa-invite-card::after {
            width: 80px;
            height: 80px;
            right: -28px;
            bottom: 78px;
            border-radius: 50%;
        }

        .aa-invite-kicker {
            color: var(--aa-gold);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .aa-invite-title {
            font-family: "Instrument Serif", serif;
            color: var(--aa-dark);
            font-size: 44px;
            font-style: italic;
            line-height: .9;
        }

        .aa-invite-line {
            width: 94px;
            height: 9px;
            border-radius: 99px;
            background: linear-gradient(90deg, var(--aa-lavender), var(--aa-gold));
        }

        .aa-invite-meta {
            color: #686a76;
            font-size: 11px;
            line-height: 1.5;
            font-weight: 700;
        }

        .aa-props-rail {
            border-left: 1px solid rgba(25, 25, 35, .08);
        }

        .aa-props-title {
            margin-bottom: 12px;
            color: #666a78;
            font-size: 10px;
            font-weight: 900;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .aa-prop-line,
        .aa-prop-pill {
            height: 9px;
            margin-bottom: 10px;
            border-radius: 99px;
            background: #dcd7cc;
        }

        .aa-prop-line:nth-child(2) {
            width: 100%;
        }

        .aa-prop-line:nth-child(3) {
            width: 72%;
        }

        .aa-prop-pill {
            height: 34px;
            background: var(--aa-dark);
        }

        .aa-floating-note {
            position: absolute;
            right: 0;
            bottom: 52px;
            width: 244px;
            border: 1px solid rgba(25, 25, 35, .11);
            border-radius: 26px;
            background: var(--aa-lavender);
            padding: 18px;
            box-shadow: var(--aa-shadow), var(--aa-inset);
            color: var(--aa-dark);
            animation: aaFloat 8s ease-in-out infinite reverse;
        }

        .aa-floating-note strong {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            line-height: 1.2;
        }

        .aa-floating-note span {
            color: rgba(25, 25, 35, .72);
            font-size: 13px;
            font-weight: 700;
        }

        .aa-shape {
            position: absolute;
            z-index: 1;
            pointer-events: none;
        }

        .aa-shape.one {
            width: 136px;
            height: 136px;
            border-radius: 50%;
            left: 5%;
            bottom: 11%;
            background: var(--aa-gold);
            box-shadow: inset 8px 8px 20px rgba(255, 255, 255, .34);
            animation: aaSpin 18s linear infinite;
        }

        .aa-shape.two {
            width: 170px;
            height: 170px;
            right: 8%;
            top: 17%;
            border: 18px solid var(--aa-lavender);
            border-radius: 46% 54% 38% 62%;
            transform: rotate(18deg);
            animation: aaMorph 9s ease-in-out infinite alternate;
        }

        .aa-shape.three {
            width: 86px;
            height: 86px;
            left: 49%;
            bottom: 9%;
            border-radius: 24px;
            background: var(--aa-mint);
            transform: rotate(16deg);
            animation: aaFloat 7s ease-in-out infinite;
        }

        .aa-section {
            position: relative;
            padding: 118px 0;
        }

        .aa-section.is-white {
            background: var(--aa-white);
        }

        .aa-section.is-cream {
            background: var(--aa-cream);
        }

        .aa-section-head {
            max-width: 780px;
            margin: 0 auto 56px;
            text-align: center;
        }

        .aa-subtitle-line {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 16px;
            color: #636574;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .aa-section-title {
            margin: 0;
            color: var(--aa-dark);
            font-size: clamp(42px, 5vw, 78px);
            line-height: .98;
            font-weight: 800;
            letter-spacing: -.06em;
        }

        .aa-section-title span {
            font-family: "Instrument Serif", serif;
            font-style: italic;
            font-weight: 400;
            letter-spacing: -.035em;
        }

        .aa-section-text {
            max-width: 650px;
            margin: 18px auto 0;
            color: var(--aa-muted);
            font-size: 17px;
            line-height: 1.75;
        }

        .aa-about-grid {
            display: grid;
            grid-template-columns: 1.05fr .95fr;
            align-items: stretch;
            gap: 28px;
        }

        .aa-big-statement {
            border: 1px solid rgba(25, 25, 35, .1);
            border-radius: var(--aa-radius-xl);
            background: var(--aa-white);
            padding: 54px;
            box-shadow: var(--aa-inset), 0 18px 50px rgba(25, 25, 35, .08);
        }

        .aa-big-statement p {
            margin: 0;
            color: var(--aa-dark);
            font-size: clamp(36px, 4.6vw, 72px);
            line-height: 1.02;
            font-weight: 800;
            letter-spacing: -.06em;
        }

        .aa-big-statement span {
            font-family: "Instrument Serif", serif;
            font-style: italic;
            font-weight: 400;
            color: var(--aa-violet);
        }

        .aa-product-video {
            position: relative;
            display: grid;
            min-height: 480px;
            place-items: center;
            border: 1px solid rgba(25, 25, 35, .12);
            border-radius: var(--aa-radius-xl);
            background:
                radial-gradient(circle at 30% 26%, rgba(255, 255, 255, .82), transparent 22%),
                linear-gradient(135deg, var(--aa-dark), #2a2a48 58%, #6c5bd0);
            padding: 34px;
            box-shadow: var(--aa-shadow);
            overflow: hidden;
        }

        .aa-product-video::before {
            content: "";
            position: absolute;
            inset: 28px;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 28px;
        }

        .aa-play {
            display: grid;
            width: 92px;
            height: 92px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .45);
            border-radius: 50%;
            background: rgba(255, 255, 255, .14);
            box-shadow: inset 1px 1px 0 rgba(255, 255, 255, .4);
            backdrop-filter: blur(10px);
        }

        .aa-play::before {
            content: "";
            width: 0;
            height: 0;
            border-top: 14px solid transparent;
            border-bottom: 14px solid transparent;
            border-left: 22px solid var(--aa-white);
            transform: translateX(3px);
        }

        .aa-stat-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-top: 28px;
        }

        .aa-stat {
            border: 1px solid rgba(25, 25, 35, .1);
            border-radius: 24px;
            background: rgba(255, 255, 255, .72);
            padding: 24px;
            box-shadow: var(--aa-inset);
        }

        .aa-stat strong {
            display: block;
            color: var(--aa-dark);
            font-size: 38px;
            line-height: 1;
            letter-spacing: -.05em;
        }

        .aa-stat span {
            display: block;
            margin-top: 8px;
            color: #696d7a;
            font-size: 13px;
            font-weight: 800;
        }

        .aa-services {
            position: relative;
            display: grid;
            gap: 18px;
        }

        .aa-service-card {
            position: relative;
            display: grid;
            grid-template-columns: 1fr auto 58px;
            align-items: center;
            gap: 18px;
            min-height: 104px;
            border: 1px solid rgba(25, 25, 35, .12);
            border-radius: 999px;
            background: rgba(255, 255, 255, .68);
            padding: 6px 8px 6px 34px;
            box-shadow: var(--aa-inset), 0 18px 45px rgba(25, 25, 35, .06);
            overflow: hidden;
            transition: border-color .28s ease, transform .28s ease;
        }

        .aa-service-card::before {
            content: "";
            position: absolute;
            inset: 6px auto 6px 6px;
            width: 62%;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(184, 180, 255, .22), rgba(184, 180, 255, .78));
            transform: scaleX(.08);
            transform-origin: left center;
            transition: transform .5s cubic-bezier(.2, .8, .2, 1);
        }

        .aa-service-card:nth-child(2)::before {
            background: linear-gradient(90deg, rgba(255, 215, 104, .18), rgba(255, 215, 104, .82));
        }

        .aa-service-card:nth-child(3)::before {
            background: linear-gradient(90deg, rgba(200, 231, 255, .18), rgba(200, 231, 255, .88));
        }

        .aa-service-card:nth-child(4)::before {
            background: linear-gradient(90deg, rgba(205, 238, 227, .2), rgba(205, 238, 227, .9));
        }

        .aa-service-card:hover {
            border-color: rgba(127, 101, 215, .45);
            transform: translateY(-2px);
        }

        .aa-service-card:hover::before {
            transform: scaleX(1);
        }

        .aa-service-title,
        .aa-service-tags,
        .aa-service-arrow {
            position: relative;
            z-index: 1;
        }

        .aa-service-title {
            color: var(--aa-dark);
            font-size: clamp(24px, 3vw, 38px);
            line-height: 1;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .aa-service-tags {
            display: flex;
            gap: 12px;
            color: #4f5360;
            font-size: 14px;
            font-weight: 800;
            white-space: nowrap;
        }

        .aa-service-tags span {
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .aa-service-tags span + span::before {
            content: "";
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: currentColor;
        }

        .aa-service-arrow {
            display: grid;
            width: 58px;
            height: 58px;
            place-items: center;
            border-radius: 50%;
            background: var(--aa-white);
            box-shadow: var(--aa-inset), 0 12px 24px rgba(25, 25, 35, .12);
            font-size: 22px;
            transition: transform .25s ease;
        }

        .aa-service-card:hover .aa-service-arrow {
            transform: translateX(3px) rotate(-12deg);
        }

        .aa-project-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 28px;
            margin-bottom: 42px;
        }

        .aa-year {
            font-size: clamp(72px, 12vw, 172px);
            line-height: .78;
            font-weight: 800;
            letter-spacing: -.08em;
        }

        .aa-year span {
            display: block;
            font-family: "Instrument Serif", serif;
            font-style: italic;
            font-weight: 400;
            color: var(--aa-violet);
        }

        .aa-project-copy {
            max-width: 420px;
            color: var(--aa-muted);
            font-size: 16px;
            line-height: 1.75;
        }

        .aa-project-strip {
            display: grid;
            grid-auto-columns: minmax(280px, 1fr);
            grid-auto-flow: column;
            gap: 18px;
            overflow-x: auto;
            padding-bottom: 18px;
            scrollbar-width: none;
        }

        .aa-project-strip::-webkit-scrollbar {
            display: none;
        }

        .aa-project-card {
            min-height: 420px;
            border: 1px solid rgba(25, 25, 35, .12);
            border-radius: 34px;
            background: var(--aa-white);
            box-shadow: var(--aa-inset), 0 20px 48px rgba(25, 25, 35, .08);
            overflow: hidden;
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .aa-project-card:hover {
            transform: translateY(-7px);
            box-shadow: var(--aa-inset), 0 28px 70px rgba(25, 25, 35, .14);
        }

        .aa-project-art {
            position: relative;
            min-height: 300px;
            overflow: hidden;
        }

        .aa-project-card.is-cream .aa-project-art {
            background: linear-gradient(135deg, #fff7e5, #f0e4d2);
        }

        .aa-project-card.is-mint .aa-project-art {
            background: linear-gradient(135deg, #d9f4ea, #fffaf0);
        }

        .aa-project-card.is-blue .aa-project-art {
            background: linear-gradient(135deg, #d7edff, #f6f7ff);
        }

        .aa-project-card.is-yellow .aa-project-art {
            background: linear-gradient(135deg, #ffe7a5, #fffaf0);
        }

        .aa-phone-template {
            position: absolute;
            left: 50%;
            top: 34px;
            width: 158px;
            height: 238px;
            border: 1px solid rgba(25, 25, 35, .12);
            border-radius: 24px;
            background: rgba(255, 255, 255, .7);
            transform: translateX(-50%) rotate(-3deg);
            box-shadow: 0 22px 46px rgba(25, 25, 35, .16);
        }

        .aa-phone-template::before,
        .aa-phone-template::after {
            content: "";
            position: absolute;
            left: 24px;
            right: 24px;
            border-radius: 999px;
            background: rgba(25, 25, 35, .14);
        }

        .aa-phone-template::before {
            top: 42px;
            height: 12px;
        }

        .aa-phone-template::after {
            bottom: 34px;
            height: 40px;
            background: linear-gradient(90deg, var(--aa-gold), var(--aa-lavender));
        }

        .aa-project-body {
            padding: 22px 24px 26px;
        }

        .aa-project-title {
            margin: 0 0 8px;
            color: var(--aa-dark);
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .aa-project-desc {
            margin: 0;
            color: var(--aa-muted);
            font-size: 14px;
            line-height: 1.55;
            font-weight: 600;
        }

        .aa-process-wrap {
            position: relative;
        }

        .aa-process-line {
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 1px;
            background: rgba(25, 25, 35, .13);
        }

        .aa-process-fill {
            position: absolute;
            left: 0;
            top: 0;
            width: 1px;
            height: 0;
            background: var(--aa-violet);
        }

        .aa-step {
            position: relative;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 70px;
            min-height: 190px;
            align-items: center;
            padding: 16px 0;
        }

        .aa-step:nth-child(even) .aa-step-card {
            grid-column: 2;
        }

        .aa-step:nth-child(even) .aa-step-number {
            grid-column: 1;
            grid-row: 1;
            justify-self: end;
        }

        .aa-step-card {
            border: 1px solid rgba(25, 25, 35, .1);
            border-radius: 30px;
            background: rgba(255, 255, 255, .76);
            padding: 32px;
            box-shadow: var(--aa-inset), 0 18px 45px rgba(25, 25, 35, .07);
        }

        .aa-step-number {
            color: rgba(25, 25, 35, .12);
            font-size: clamp(64px, 8vw, 128px);
            line-height: .8;
            font-weight: 800;
            letter-spacing: -.08em;
        }

        .aa-step-card h3 {
            margin: 0 0 10px;
            color: var(--aa-dark);
            font-size: 28px;
            letter-spacing: -.04em;
        }

        .aa-step-card p {
            margin: 0;
            color: var(--aa-muted);
            line-height: 1.65;
        }

        .aa-testimonial-grid {
            display: grid;
            grid-template-columns: .85fr 1.15fr;
            gap: 26px;
            align-items: stretch;
        }

        .aa-review-tabs {
            display: grid;
            gap: 12px;
        }

        .aa-review-tab {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            border: 1px solid rgba(25, 25, 35, .1);
            border-radius: 999px;
            background: var(--aa-white);
            color: var(--aa-dark);
            padding: 18px 22px;
            cursor: pointer;
            box-shadow: var(--aa-inset);
            transition: background .25s ease, transform .25s ease;
        }

        .aa-review-tab.is-active,
        .aa-review-tab:hover {
            background: var(--aa-dark);
            color: var(--aa-white);
            transform: translateX(4px);
        }

        .aa-review-tab span {
            color: inherit;
            font-weight: 800;
        }

        .aa-review-card {
            position: relative;
            min-height: 430px;
            border: 1px solid rgba(25, 25, 35, .1);
            border-radius: var(--aa-radius-xl);
            background: var(--aa-white);
            padding: 48px;
            box-shadow: var(--aa-shadow), var(--aa-inset);
            overflow: hidden;
        }

        .aa-review-card::after {
            content: "";
            position: absolute;
            right: -80px;
            bottom: -80px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: var(--aa-lavender);
            opacity: .5;
        }

        .aa-stars {
            display: flex;
            gap: 7px;
            margin-bottom: 34px;
        }

        .aa-stars span {
            display: grid;
            width: 30px;
            height: 30px;
            place-items: center;
            border-radius: 50%;
            background: var(--aa-gold);
            color: var(--aa-dark);
            font-size: 14px;
            font-weight: 900;
        }

        .aa-review-quote {
            position: relative;
            z-index: 1;
            margin: 0;
            color: var(--aa-dark);
            font-size: clamp(28px, 3.2vw, 48px);
            line-height: 1.08;
            font-weight: 800;
            letter-spacing: -.05em;
        }

        .aa-review-author {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 42px;
            color: var(--aa-muted);
            font-weight: 800;
        }

        .aa-review-author::before {
            content: "";
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--aa-dark), var(--aa-violet));
        }

        .aa-cta {
            position: relative;
            min-height: 680px;
            background: var(--aa-dark);
            color: var(--aa-white);
            overflow: clip;
        }

        .aa-cta-sticky {
            position: sticky;
            top: 0;
            display: grid;
            min-height: 100vh;
            place-items: center;
            padding: 120px 0;
        }

        .aa-cta-letter {
            position: absolute;
            color: rgba(255, 255, 255, .06);
            font-size: clamp(160px, 22vw, 360px);
            line-height: .8;
            font-weight: 800;
            letter-spacing: -.1em;
        }

        .aa-cta-letter.left {
            left: 2vw;
            top: 14vh;
        }

        .aa-cta-letter.right {
            right: 3vw;
            bottom: 10vh;
        }

        .aa-cta-card {
            position: relative;
            z-index: 2;
            width: min(100% - 30px, 760px);
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 38px;
            background:
                radial-gradient(circle at 50% 0, rgba(215, 162, 55, .22), transparent 30%),
                rgba(255, 255, 255, .08);
            padding: clamp(34px, 5vw, 64px);
            text-align: center;
            box-shadow: inset 1px 1px 0 rgba(255, 255, 255, .22), 0 34px 90px rgba(0, 0, 0, .24);
            backdrop-filter: blur(18px);
        }

        .aa-cta-card h2 {
            margin: 0;
            font-size: clamp(44px, 5vw, 80px);
            line-height: .96;
            letter-spacing: -.06em;
        }

        .aa-cta-card h2 span {
            font-family: "Instrument Serif", serif;
            font-style: italic;
            font-weight: 400;
            color: var(--aa-gold);
        }

        .aa-cta-card p {
            max-width: 560px;
            margin: 18px auto 28px;
            color: rgba(255, 255, 255, .72);
            line-height: 1.7;
        }

        .aa-footer {
            background: var(--aa-dark);
            color: rgba(255, 255, 255, .72);
            padding: 60px 0 34px;
        }

        .aa-footer-inner {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: end;
            gap: 28px;
            border-top: 1px solid rgba(255, 255, 255, .12);
            padding-top: 28px;
        }

        .aa-footer-title {
            color: var(--aa-white);
            font-size: clamp(52px, 10vw, 144px);
            line-height: .82;
            font-weight: 800;
            letter-spacing: -.08em;
        }

        .aa-footer-title span {
            font-family: "Instrument Serif", serif;
            font-style: italic;
            font-weight: 400;
            color: var(--aa-gold);
        }

        .aa-footer-links {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
            justify-content: flex-end;
            font-size: 13px;
            font-weight: 800;
        }

        .aa-footer-links a {
            transition: color .22s ease;
        }

        .aa-footer-links a:hover {
            color: var(--aa-white);
        }

        .aa-reveal {
            opacity: 0;
            transform: translateY(34px);
            transition: opacity .7s ease, transform .7s cubic-bezier(.2, .8, .2, 1);
        }

        .aa-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        @keyframes aaFloat {
            0%, 100% {
                transform: translate3d(0, 0, 0) rotate(0deg);
            }

            50% {
                transform: translate3d(0, -18px, 0) rotate(1deg);
            }
        }

        @keyframes aaSpin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes aaMorph {
            to {
                border-radius: 62% 38% 52% 48%;
                transform: rotate(-12deg) scale(1.05);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: .001ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: .001ms !important;
            }

            .aa-reveal {
                opacity: 1;
                transform: none;
            }
        }

        @media (max-width: 1080px) {
            .aa-header {
                border-radius: 28px;
            }

            .aa-nav {
                display: none;
            }

            .aa-hero-vh,
            .aa-hero {
                min-height: auto;
                height: auto;
            }

            .aa-hero {
                position: relative;
                padding: 120px 0 80px;
            }

            .aa-hero-wrap {
                grid-template-columns: 1fr;
                padding-top: 60px;
            }

            .aa-hero-art {
                min-height: 540px;
            }

            .aa-studio-card {
                left: 0;
                right: auto;
            }

            .aa-floating-note {
                right: 20px;
            }

            .aa-about-grid,
            .aa-testimonial-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .aa-header-shell {
                inset: 10px 0 auto;
            }

            .aa-header {
                min-height: 62px;
                padding: 9px 10px 9px 14px;
            }

            .aa-brand {
                min-width: auto;
                font-size: 16px;
            }

            .aa-brand-mark {
                width: 36px;
                height: 36px;
            }

            .aa-auth {
                display: none;
            }

            .aa-button {
                min-height: 42px;
                padding: 0 15px;
                font-size: 13px;
            }

            .aa-button-arrow {
                display: none;
            }

            .aa-hero {
                padding-top: 92px;
            }

            .aa-eyebrow::before {
                width: 36px;
            }

            .aa-hero-text {
                font-size: 16px;
            }

            .aa-hero-art {
                min-height: 470px;
            }

            .aa-studio-card {
                width: 100%;
                inset: 0 auto auto 0;
            }

            .aa-studio-body {
                grid-template-columns: 52px 1fr;
            }

            .aa-props-rail {
                display: none;
            }

            .aa-floating-note {
                right: 8px;
                bottom: 0;
                width: 210px;
            }

            .aa-shape.one,
            .aa-shape.two {
                display: none;
            }

            .aa-section {
                padding: 78px 0;
            }

            .aa-big-statement {
                padding: 34px 26px;
            }

            .aa-product-video {
                min-height: 360px;
            }

            .aa-stat-row {
                grid-template-columns: 1fr;
            }

            .aa-service-card {
                grid-template-columns: 1fr 48px;
                border-radius: 28px;
                padding: 22px;
            }

            .aa-service-tags {
                grid-column: 1 / -1;
                flex-wrap: wrap;
                white-space: normal;
            }

            .aa-service-arrow {
                width: 48px;
                height: 48px;
            }

            .aa-project-head {
                display: block;
            }

            .aa-project-copy {
                margin-top: 24px;
            }

            .aa-process-line {
                left: 19px;
            }

            .aa-step,
            .aa-step:nth-child(even) .aa-step-card,
            .aa-step:nth-child(even) .aa-step-number {
                display: block;
            }

            .aa-step {
                padding-left: 46px;
            }

            .aa-step-number {
                margin-bottom: 12px;
                font-size: 58px;
            }

            .aa-review-card {
                min-height: 360px;
                padding: 34px 26px;
            }

            .aa-footer-inner {
                grid-template-columns: 1fr;
            }

            .aa-footer-links {
                justify-content: flex-start;
            }
        }

        @media (max-width: 430px) {
            .aa-container {
                width: min(100% - 22px, 1320px);
            }

            .aa-header {
                gap: 8px;
            }

            .aa-brand span:last-child {
                max-width: 88px;
                overflow: hidden;
                white-space: nowrap;
            }

            .aa-hero h1 {
                font-size: 56px;
            }

            .aa-hero-cta {
                align-items: stretch;
                flex-direction: column;
            }

            .aa-hero-cta .aa-button {
                width: 100%;
            }

            .aa-project-strip {
                grid-auto-columns: 84%;
            }
        }
    </style>
</head>
<body>
    <div class="aa-page">
        <header class="aa-header-shell">
            <div class="aa-container">
                <div class="aa-header" data-aa-header>
                    <a class="aa-brand" href="<?= site_url('home-v2') ?>">
                        <span class="aa-brand-mark"><span></span></span>
                        <span>AdaAcara</span>
                    </a>
                    <nav class="aa-nav" aria-label="Navigasi Home V2">
                        <a class="is-active" href="<?= site_url('home-v2') ?>">Home</a>
                        <a href="<?= site_url('templates') ?>">Template</a>
                        <a href="<?= site_url('plans') ?>">Harga</a>
                        <a href="<?= site_url('guides') ?>">Panduan</a>
                    </nav>
                    <div class="aa-actions">
                        <a class="aa-auth" href="<?= esc($loginUrl, 'attr') ?>"><?= $isLoggedIn ? 'Dashboard' : 'Masuk' ?></a>
                        <a class="aa-button" href="<?= esc($startUrl, 'attr') ?>">
                            Mulai Gratis
                            <span class="aa-button-arrow">></span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <div class="aa-hero-vh">
                <section class="aa-hero">
                    <div class="aa-hero-bg-grid"></div>
                    <div class="aa-shape one"></div>
                    <div class="aa-shape two"></div>
                    <div class="aa-shape three"></div>
                    <div class="aa-container">
                        <div class="aa-hero-wrap">
                            <div class="aa-hero-copy aa-reveal">
                                <div class="aa-eyebrow">
                                    <span class="aa-eyebrow-dot"></span>
                                    AdaAcara Visual Studio
                                </div>
                                <h1>Buat website undangan yang <span>hidup</span>.</h1>
                                <p class="aa-hero-text">Platform visual untuk membuat undangan website dari template atau canvas kosong. Edit desain, tambah RSVP, guestbook, musik, QR code, gift, lalu publish jadi link.</p>
                                <div class="aa-hero-cta">
                                    <a class="aa-button" href="<?= esc($startUrl, 'attr') ?>">Buat Undangan <span class="aa-button-arrow">></span></a>
                                    <a class="aa-button is-light" href="<?= site_url('templates') ?>">Lihat Template</a>
                                </div>
                                <div class="aa-hero-social">
                                    <div class="aa-avatar-stack" aria-hidden="true">
                                        <span class="aa-avatar">RS</span>
                                        <span class="aa-avatar">AI</span>
                                        <span class="aa-avatar">QR</span>
                                    </div>
                                    <div class="aa-rating">Dipakai untuk wedding, aqiqah, ulang tahun, seminar, dan creator template.</div>
                                </div>
                            </div>
                            <div class="aa-hero-art aa-reveal" data-aa-parallax>
                                <div class="aa-studio-card">
                                    <div class="aa-studio-top">
                                        <div class="aa-window-dots"><span></span><span></span><span></span></div>
                                        <div class="aa-publish-pill">Publish</div>
                                    </div>
                                    <div class="aa-studio-body">
                                        <div class="aa-tool-rail">
                                            <div class="aa-tool is-active">T</div>
                                            <div class="aa-tool">Img</div>
                                            <div class="aa-tool">RS</div>
                                            <div class="aa-tool">QR</div>
                                            <div class="aa-tool">AI</div>
                                        </div>
                                        <div class="aa-canvas-wrap">
                                            <div class="aa-invite-card">
                                                <div class="aa-invite-kicker">The Wedding</div>
                                                <div class="aa-invite-title">Alya<br>Raka</div>
                                                <div class="aa-invite-line"></div>
                                                <div class="aa-invite-meta">12.10.2026<br>adaacara.com/u/alya-raka</div>
                                            </div>
                                        </div>
                                        <div class="aa-props-rail">
                                            <div class="aa-props-title">Properties</div>
                                            <div class="aa-prop-line"></div>
                                            <div class="aa-prop-line"></div>
                                            <div class="aa-prop-pill"></div>
                                            <div class="aa-prop-line"></div>
                                            <div class="aa-prop-line"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="aa-floating-note">
                                    <strong>RSVP, ucapan, musik, gift, QR.</strong>
                                    <span>Semuanya ikut tersimpan di link undangan.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <section class="aa-section is-white">
                <div class="aa-container">
                    <div class="aa-about-grid">
                        <div class="aa-big-statement aa-reveal">
                            <p>AdaAcara bukan katalog undangan biasa. Ini <span>studio visual</span> untuk membuat website acara yang terasa personal.</p>
                            <div class="aa-stat-row">
                                <div class="aa-stat"><strong>10rb+</strong><span>undangan dibuat</span></div>
                                <div class="aa-stat"><strong>100+</strong><span>template siap edit</span></div>
                                <div class="aa-stat"><strong>24/7</strong><span>online sebagai link</span></div>
                            </div>
                        </div>
                        <div class="aa-product-video aa-reveal">
                            <div class="aa-play" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="aa-section is-cream">
                <div class="aa-container">
                    <div class="aa-section-head aa-reveal">
                        <div class="aa-subtitle-line">Core Features</div>
                        <h2 class="aa-section-title">Semua fitur utama dalam satu <span>alur</span>.</h2>
                        <p class="aa-section-text">Dari desain sampai publish, setiap bagian dibuat sebagai workflow visual yang mudah dipahami user.</p>
                    </div>
                    <div class="aa-services">
                        <?php foreach ($services as $service): ?>
                            <a class="aa-service-card aa-reveal" href="<?= site_url('templates') ?>">
                                <div class="aa-service-title"><?= esc($service[0]) ?></div>
                                <div class="aa-service-tags">
                                    <span><?= esc($service[1]) ?></span>
                                    <span><?= esc($service[2]) ?></span>
                                    <span><?= esc($service[3]) ?></span>
                                </div>
                                <div class="aa-service-arrow">></div>
                            </a>
                        <?php endforeach ?>
                    </div>
                </div>
            </section>

            <section class="aa-section is-white">
                <div class="aa-container">
                    <div class="aa-project-head aa-reveal">
                        <div class="aa-year">2026 <span>work</span></div>
                        <p class="aa-project-copy">Template dibuat seperti produk siap pakai, tapi tetap bebas dirombak di editor. Ini contoh kartu preview ala project showcase.</p>
                    </div>
                    <div class="aa-project-strip aa-reveal">
                        <?php foreach ($projects as $project): ?>
                            <a class="aa-project-card is-<?= esc($project[2], 'attr') ?>" href="<?= site_url('templates') ?>">
                                <div class="aa-project-art"><div class="aa-phone-template"></div></div>
                                <div class="aa-project-body">
                                    <h3 class="aa-project-title"><?= esc($project[0]) ?></h3>
                                    <p class="aa-project-desc"><?= esc($project[1]) ?></p>
                                </div>
                            </a>
                        <?php endforeach ?>
                    </div>
                </div>
            </section>

            <section class="aa-section is-cream">
                <div class="aa-container">
                    <div class="aa-section-head aa-reveal">
                        <div class="aa-subtitle-line">How It Works</div>
                        <h2 class="aa-section-title">Dari ide acara jadi link yang <span>siap dibagikan</span>.</h2>
                    </div>
                    <div class="aa-process-wrap aa-reveal" data-aa-process>
                        <div class="aa-process-line"><div class="aa-process-fill" data-aa-process-fill></div></div>
                        <?php foreach ($steps as $step): ?>
                            <div class="aa-step">
                                <div class="aa-step-card">
                                    <h3><?= esc($step[1]) ?></h3>
                                    <p><?= esc($step[2]) ?></p>
                                </div>
                                <div class="aa-step-number"><?= esc($step[0]) ?></div>
                            </div>
                        <?php endforeach ?>
                    </div>
                </div>
            </section>

            <section class="aa-section is-white">
                <div class="aa-container">
                    <div class="aa-section-head aa-reveal">
                        <div class="aa-subtitle-line">Experience</div>
                        <h2 class="aa-section-title">Lebih terasa seperti tool, bukan halaman katalog.</h2>
                    </div>
                    <div class="aa-testimonial-grid aa-reveal">
                        <div class="aa-review-tabs" role="tablist">
                            <?php foreach ($reviews as $index => $review): ?>
                                <button class="aa-review-tab<?= $index === 0 ? ' is-active' : '' ?>" type="button" data-aa-review="<?= $index ?>">
                                    <span><?= esc($review[2]) ?></span>
                                    <span>0<?= $index + 1 ?></span>
                                </button>
                            <?php endforeach ?>
                        </div>
                        <div class="aa-review-card">
                            <div class="aa-stars" aria-hidden="true"><span>*</span><span>*</span><span>*</span><span>*</span><span>*</span></div>
                            <p class="aa-review-quote" data-aa-review-title><?= esc($reviews[0][0]) ?></p>
                            <div class="aa-review-author" data-aa-review-text><?= esc($reviews[0][1]) ?></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="aa-cta">
                <div class="aa-cta-sticky">
                    <div class="aa-cta-letter left">A</div>
                    <div class="aa-cta-letter right">K</div>
                    <div class="aa-cta-card aa-reveal">
                        <div class="aa-subtitle-line">Start Your Event</div>
                        <h2>Siap membuat undangan website yang <span>berkesan</span>?</h2>
                        <p>Mulai dari template atau canvas kosong. Edit visual, aktifkan fitur tamu, lalu publish link AdaAcara.</p>
                        <div class="aa-hero-cta" style="justify-content:center">
                            <a class="aa-button" href="<?= esc($startUrl, 'attr') ?>">Buat Gratis <span class="aa-button-arrow">></span></a>
                            <a class="aa-button is-light" href="<?= site_url('plans') ?>">Lihat Harga</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="aa-footer">
            <div class="aa-container">
                <div class="aa-footer-inner">
                    <div>
                        <div class="aa-footer-title">Ada<span>Acara</span></div>
                        <p>Editor visual & marketplace undangan digital untuk kreator Indonesia.</p>
                    </div>
                    <nav class="aa-footer-links" aria-label="Footer">
                        <a href="<?= site_url('templates') ?>">Template</a>
                        <a href="<?= site_url('plans') ?>">Harga</a>
                        <a href="<?= site_url('creator/apply') ?>">Creator</a>
                        <a href="<?= site_url('privacy') ?>">Privasi</a>
                        <a href="<?= site_url('terms') ?>">Syarat</a>
                    </nav>
                </div>
            </div>
        </footer>
    </div>

    <script>
        (() => {
            const header = document.querySelector('[data-aa-header]');
            const revealItems = Array.from(document.querySelectorAll('.aa-reveal'));
            const process = document.querySelector('[data-aa-process]');
            const processFill = document.querySelector('[data-aa-process-fill]');
            const parallax = document.querySelector('[data-aa-parallax]');
            const reviews = <?= json_encode($reviews, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
            const reviewTitle = document.querySelector('[data-aa-review-title]');
            const reviewText = document.querySelector('[data-aa-review-text]');
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const updateHeader = () => {
                if (!header) return;
                header.classList.toggle('is-scrolled', window.scrollY > 12);
            };

            const updateProcess = () => {
                if (!process || !processFill) return;
                const rect = process.getBoundingClientRect();
                const viewport = window.innerHeight || document.documentElement.clientHeight;
                const progress = Math.min(1, Math.max(0, (viewport * .72 - rect.top) / Math.max(1, rect.height)));
                processFill.style.height = `${progress * 100}%`;
            };

            const updateParallax = () => {
                if (!parallax || reduceMotion) return;
                const offset = Math.min(34, Math.max(-34, window.scrollY * -.035));
                parallax.style.transform = `translate3d(0, ${offset}px, 0)`;
            };

            if (!reduceMotion && 'IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    });
                }, { threshold: .16 });

                revealItems.forEach((item, index) => {
                    item.style.transitionDelay = `${Math.min(index % 4, 3) * 80}ms`;
                    observer.observe(item);
                });
            } else {
                revealItems.forEach((item) => item.classList.add('is-visible'));
            }

            document.querySelectorAll('[data-aa-review]').forEach((tab) => {
                tab.addEventListener('click', () => {
                    const index = Number(tab.dataset.aaReview || 0);
                    const data = reviews[index] || reviews[0];
                    document.querySelectorAll('[data-aa-review]').forEach((item) => item.classList.toggle('is-active', item === tab));
                    if (!reviewTitle || !reviewText || !data) return;
                    reviewTitle.textContent = data[0];
                    reviewText.textContent = data[1];
                    if (!reduceMotion) {
                        reviewTitle.animate([{ opacity: 0, transform: 'translateY(14px)' }, { opacity: 1, transform: 'translateY(0)' }], { duration: 320, easing: 'ease-out' });
                        reviewText.animate([{ opacity: 0, transform: 'translateY(12px)' }, { opacity: 1, transform: 'translateY(0)' }], { duration: 380, easing: 'ease-out' });
                    }
                });
            });

            let ticking = false;
            const onScroll = () => {
                if (ticking) return;
                ticking = true;
                requestAnimationFrame(() => {
                    updateHeader();
                    updateProcess();
                    updateParallax();
                    ticking = false;
                });
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', onScroll);
            onScroll();
        })();
    </script>
</body>
</html>
