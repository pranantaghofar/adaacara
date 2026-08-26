<?php helper('aa_icon'); ?>

<footer class="aa-site-footer">
    <style>
        .aa-site-footer {
            border-top: 1px solid rgba(217, 204, 244, .72);
            background:
                radial-gradient(circle at 15% 0%, rgba(143, 101, 223, .14), transparent 28rem),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            color: #475569;
        }

        .aa-site-footer a {
            color: inherit;
            text-decoration: none;
            transition: color .18s ease, background .18s ease;
        }

        .aa-site-footer a:hover {
            color: #7550c4;
        }

        .aa-site-footer-shell {
            width: min(1800px, calc(100% - 70px));
            margin: 0 auto;
        }

        .aa-site-footer-main {
            display: grid;
            grid-template-columns: minmax(260px, 1.4fr) repeat(3, minmax(0, .8fr));
            gap: 36px;
            padding: 52px 0 36px;
        }

        .aa-site-footer-logo {
            width: 154px;
            height: auto;
            object-fit: contain;
        }

        .aa-site-footer-desc {
            max-width: 360px;
            margin: 18px 0 0;
            color: #334155;
            font-size: 15px;
            font-weight: 650;
            line-height: 1.7;
        }

        .aa-site-footer-contact {
            display: grid;
            gap: 10px;
            margin-top: 22px;
        }

        .aa-site-footer-contact a,
        .aa-site-footer-contact span {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #64748b;
            font-size: 14px;
            font-weight: 700;
        }

        .aa-site-footer-contact-icon {
            display: inline-flex;
            width: 20px;
            height: 20px;
            flex: 0 0 20px;
            align-items: center;
            justify-content: center;
            color: #8f65df;
        }

        .aa-site-footer-contact-icon svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
        }

        .aa-site-footer-contact-icon.is-email {
            color: #7550c4;
        }

        .aa-site-footer-contact-icon.is-instagram {
            color: #e1306c;
        }

        .aa-site-footer-contact-icon.is-whatsapp {
            color: #16a34a;
        }

        .aa-site-footer-contact-icon.is-threads {
            color: #0f172a;
        }

        .aa-site-footer-title {
            margin: 0 0 14px;
            color: #7550c4;
            font-size: 12px;
            font-weight: 950;
            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .aa-site-footer-list {
            display: grid;
            gap: 10px;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .aa-site-footer-list a {
            color: #475569;
            font-size: 14px;
            font-weight: 750;
        }

        .aa-site-footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            border-top: 1px solid rgba(217, 204, 244, .72);
            padding: 20px 0 28px;
            color: #64748b;
            font-size: 13px;
            font-weight: 650;
        }

        .aa-site-footer-legal {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 16px;
        }

        @media (max-width: 820px) {
            .aa-site-footer-shell {
                width: min(100% - 36px, 1800px);
            }

            .aa-site-footer-main {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 30px 22px;
                padding: 38px 0 28px;
            }

            .aa-site-footer-brand {
                grid-column: 1 / -1;
            }

            .aa-site-footer-desc {
                max-width: 620px;
                margin-top: 14px;
                font-size: 14px;
                line-height: 1.65;
            }

            .aa-site-footer-contact {
                gap: 8px;
                margin-top: 16px;
            }

            .aa-site-footer-contact a,
            .aa-site-footer-contact span {
                align-items: flex-start;
                font-size: 13px;
                line-height: 1.45;
            }

            .aa-site-footer-title {
                margin-bottom: 10px;
            }

            .aa-site-footer-list {
                gap: 8px;
            }

            .aa-site-footer-bottom {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
                padding: 18px 0 24px;
                line-height: 1.55;
            }

            .aa-site-footer-legal {
                justify-content: flex-start;
                gap: 10px 14px;
            }
        }

        @media (max-width: 560px) {
            .aa-site-footer-shell {
                width: min(100% - 28px, 1800px);
            }

            .aa-site-footer-main {
                grid-template-columns: 1fr;
                gap: 24px;
                padding: 34px 0 24px;
            }

            .aa-site-footer-logo {
                width: 138px;
            }

            .aa-site-footer-desc {
                font-size: 13px;
            }

            .aa-site-footer-contact {
                margin-top: 14px;
            }

            .aa-site-footer-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 9px 14px;
            }

            .aa-site-footer-list a {
                font-size: 13px;
            }

            .aa-site-footer-bottom {
                padding-bottom: 22px;
                font-size: 12px;
            }

            .aa-site-footer-legal {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
                gap: 8px 14px;
            }
        }

        @media (max-width: 380px) {
            .aa-site-footer-list,
            .aa-site-footer-legal {
                grid-template-columns: 1fr;
            }
        }

        html[data-aa-home-theme="dark"] .aa-site-footer,
        html[data-aa-public-theme="dark"] .aa-site-footer {
            border-top-color: rgba(148, 163, 184, .18);
            background:
                radial-gradient(circle at 15% 0%, rgba(143, 101, 223, .16), transparent 28rem),
                linear-gradient(180deg, #0b1220 0%, #070b12 100%);
            color: #a8b5c7;
        }

        html[data-aa-home-theme="dark"] .aa-site-footer-logo,
        html[data-aa-public-theme="dark"] .aa-site-footer-logo {
            filter: invert(1) brightness(2.05) contrast(.92);
        }

        html[data-aa-home-theme="dark"] .aa-site-footer-title,
        html[data-aa-public-theme="dark"] .aa-site-footer-title {
            color: #f8fafc;
        }

        html[data-aa-home-theme="dark"] .aa-site-footer-desc,
        html[data-aa-home-theme="dark"] .aa-site-footer-list a,
        html[data-aa-public-theme="dark"] .aa-site-footer-desc,
        html[data-aa-public-theme="dark"] .aa-site-footer-list a {
            color: #cbd5e1;
        }

        html[data-aa-home-theme="dark"] .aa-site-footer-contact a,
        html[data-aa-home-theme="dark"] .aa-site-footer-contact span,
        html[data-aa-home-theme="dark"] .aa-site-footer-bottom,
        html[data-aa-public-theme="dark"] .aa-site-footer-contact a,
        html[data-aa-public-theme="dark"] .aa-site-footer-contact span,
        html[data-aa-public-theme="dark"] .aa-site-footer-bottom {
            color: #94a3b8;
        }

        html[data-aa-home-theme="dark"] .aa-site-footer-bottom,
        html[data-aa-public-theme="dark"] .aa-site-footer-bottom {
            border-top-color: rgba(148, 163, 184, .18);
        }
    </style>

    <div class="aa-site-footer-shell">
        <div class="aa-site-footer-main">
            <div class="aa-site-footer-brand">
                <img class="aa-site-footer-logo" src="<?= aa_asset_url('assets/img/adaacara-logo.png') ?>" alt="AdaAcara" loading="lazy">
                <p class="aa-site-footer-desc">Satu tempat untuk membuat berbagai pengalaman digital. Mulai dari template, desain sebebas yang kamu mau, lalu publish menjadi website milikmu sendiri.</p>
                <div class="aa-site-footer-contact">
                    <a href="mailto:hello@adaacara.com">
                        <span class="aa-site-footer-contact-icon is-email"><?= aa_phosphor_icon('envelope', ['strokeWidth' => '2.1']) ?></span>
                        hello@adaacara.com
                    </a>
                    <a href="https://www.instagram.com/adaacara.official/" target="_blank" rel="noopener">
                        <span class="aa-site-footer-contact-icon is-instagram"><?= aa_phosphor_icon('instagram-logo', ['strokeWidth' => '2.1']) ?></span>
                        Instagram
                    </a>
                    <a href="https://wa.me/62895392291896" target="_blank" rel="noopener">
                        <span class="aa-site-footer-contact-icon is-whatsapp"><?= aa_phosphor_icon('whatsapp-logo', ['strokeWidth' => '2.1']) ?></span>
                        WhatsApp
                    </a>
                    <a href="https://www.threads.com/@adaacara.official" target="_blank" rel="noopener">
                        <span class="aa-site-footer-contact-icon is-threads"><?= aa_phosphor_icon('threads-logo', ['strokeWidth' => '2.1']) ?></span>
                        Threads
                    </a>
                </div>
            </div>

            <nav aria-label="Produk">
                <h2 class="aa-site-footer-title">Produk</h2>
                <ul class="aa-site-footer-list">
                    <li><a href="<?= site_url('creator/apply') ?>">Daftar Kreator</a></li>
                    <li><a href="<?= site_url('undangan-digital') ?>">Undangan Digital</a></li>
                    <li><a href="<?= site_url('fitur/photobooth-digital') ?>">Photobooth Digital</a></li>
                    <li><a href="<?= site_url('templates?type=business_profile') ?>">Business Profile</a></li>
                    <li><a href="<?= site_url('fitur/galeri-klien-fotografer') ?>">Galeri Klien Fotografer</a></li>
                    <li><a href="<?= site_url('editor-undangan-digital') ?>">Editor Undangan</a></li>
                    <li><a href="<?= site_url('fitur/acara-ai') ?>">AdaAcara AI</a></li>
                </ul>
            </nav>

            <nav aria-label="Bantuan">
                <h2 class="aa-site-footer-title">Bantuan</h2>
                <ul class="aa-site-footer-list">
                    <li><a href="<?= site_url('about-us') ?>">About Us</a></li>
                    <li><a href="<?= site_url('panduan') ?>">Panduan</a></li>
                    <li><a href="<?= site_url('terms') ?>">Syarat & Ketentuan</a></li>
                    <li><a href="<?= site_url('privacy') ?>">Privasi</a></li>
                </ul>
            </nav>
        </div>

        <div class="aa-site-footer-bottom">
            <span>© <?= date('Y') ?> adaAcara.com - All Right Reserved</span>
            <div class="aa-site-footer-legal">
                <a href="<?= site_url('terms') ?>">Syarat & Ketentuan</a>
                <a href="<?= site_url('privacy') ?>">Kebijakan Privasi</a>
                <a href="<?= site_url('cookies') ?>">Cookie</a>
                <span>Made in Indonesia</span>
            </div>
        </div>
    </div>
</footer>
