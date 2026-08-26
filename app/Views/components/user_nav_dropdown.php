<?php
    $active = (string) ($active ?? '');
    $hidePremium = (bool) ($hidePremium ?? false);
    $isLoggedIn = (bool) (session()->get('isLoggedIn') ?? session()->get('userId'));
    $currentUserId = (int) (session()->get('userId') ?? 0);
    $userDisplayName = trim((string) (session()->get('userName') ?? 'Pengguna AdaAcara'));
    $userEmail = trim((string) (session()->get('userEmail') ?? ''));
    $userInitial = strtoupper(substr($userDisplayName !== '' ? $userDisplayName : 'A', 0, 1));
    $isActiveCreator = false;
    $hasActivePublishPlan = false;
    if ($isLoggedIn && $currentUserId > 0) {
        try {
            $isActiveCreator = (new \App\Libraries\SellerTemplateService())->isActiveCreator($currentUserId);
            $activeSubscription = (new \App\Models\UserSubscriptionModel())->activeWithPlanByUser($currentUserId);
            $activePlanKey = strtolower(trim((string) ($activeSubscription['plan_slug'] ?? $activeSubscription['plan_name'] ?? '')));
            $hasActivePublishPlan = $activeSubscription !== null && $activePlanKey !== 'creator';
        } catch (\Throwable) {
            $isActiveCreator = false;
            $hasActivePublishPlan = false;
        }
    }
    $creatorMenuUrl = site_url('creator/dashboard');
    $creatorMenuLabel = $isActiveCreator ? 'Dashboard Creator' : 'Daftar Creator';
    $creatorLatestApplication = null;
    $creatorHasPendingApplication = false;
    $creatorActionLabel = 'Daftar Creator';
    $creatorActionClass = 'aa-creator-modal-action';
    $creatorActionDisabled = false;
    $creatorModalError = (string) (session()->getFlashdata('creator_modal_error') ?? '');
    $creatorModalSuccess = (string) (session()->getFlashdata('creator_modal_success') ?? '');
    if ($isLoggedIn && ! $isActiveCreator && ! $hasActivePublishPlan && $currentUserId > 0) {
        try {
            $db = db_connect();
            if ($db->tableExists('creator_applications')) {
                $applicationModel = new \App\Models\CreatorApplicationModel();
                $creatorLatestApplication = $applicationModel->latestForUser($currentUserId);
                $creatorHasPendingApplication = $creatorLatestApplication !== null
                    && (string) ($creatorLatestApplication['status'] ?? '') === 'pending';
                if ($creatorHasPendingApplication) {
                    $creatorActionLabel = 'Menunggu approve admin';
                    $creatorActionClass .= ' is-disabled';
                    $creatorActionDisabled = true;
                }
            }
        } catch (\Throwable) {
            $creatorLatestApplication = null;
            $creatorHasPendingApplication = false;
        }
    }
    $menuId = 'aaUserNavMenu' . substr(md5((string) microtime(true) . random_int(1, 999999)), 0, 8);
    $creatorModalId = $menuId . 'CreatorModal';
    $icon = static function (string $name, string $class = 'h-4 w-4'): string {
        $icons = [
            'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
            'template' => '<rect x="3" y="4" width="18" height="16" rx="3"/><path d="M3 10h18M9 10v10"/>',
            'creator' => '<path d="M12 3l2.5 5.1 5.6.8-4 3.9.9 5.5-5-2.6-5 2.6.9-5.5-4-3.9 5.6-.8L12 3Z"/>',
            'crown' => '<path d="m3 7 4.5 4L12 4l4.5 7L21 7l-2 12H5L3 7Z"/><path d="M5 19h14"/>',
            'package' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="M4 7.5 12 12l8-4.5M12 12v9"/>',
            'order' => '<path d="M7 7h14l-2 8H8L6 3H3"/><circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>',
            'dashboard' => '<path d="M4 13h6V4H4v9ZM14 20h6V4h-6v16ZM4 20h6v-3H4v3Z"/>',
            'login' => '<path d="M10 17v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-6a2 2 0 0 0-2 2v1"/><path d="M3 12h12m0 0-3-3m3 3-3 3"/>',
            'register' => '<path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M19 8v6M16 11h6"/>',
            'logout' => '<path d="M14 8V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-2"/><path d="M9 12h12m0 0-3-3m3 3-3 3"/>',
            'chevron' => '<path d="m6 9 6 6 6-6"/>',
        ];

        return '<svg class="' . esc($class, 'attr') . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['menu']) . '</svg>';
    };
    $linkClass = static function (string $key) use ($active): string {
        $base = 'aa-user-nav-link';
        return $active === $key ? $base . ' is-active' : $base;
    };
?>
<style>
    .aa-user-nav {
        position: relative;
        z-index: 35;
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
    }

    .aa-user-nav-toggle {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border: 1px solid #fde68a;
        border-radius: 999px;
        background: #ffffff;
        color: #0f172a;
        padding: 5px 10px 5px 6px;
        font-size: 13px;
        font-weight: 900;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .10);
        transition: .16s ease;
    }

    .aa-user-nav-toggle:hover,
    .aa-user-nav.is-open .aa-user-nav-toggle {
        border-color: #8f65df;
        color: #8f65df;
        transform: translateY(-1px);
    }

    .aa-user-nav-toggle svg,
    .aa-user-nav-link svg,
    .aa-user-nav-link-button svg {
        width: 16px;
        height: 16px;
        flex: 0 0 auto;
    }

    .aa-user-nav-avatar {
        display: inline-grid;
        width: 32px;
        height: 32px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 999px;
        background: linear-gradient(135deg, #0f172a 0%, #14532d 48%, #8f65df 100%);
        color: #ffffff;
        font-size: 13px;
        font-weight: 950;
        letter-spacing: -.02em;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .22);
    }

    .aa-user-nav-identity {
        display: grid;
        min-width: 0;
        max-width: 178px;
        gap: 2px;
        text-align: left;
        line-height: 1.05;
    }

    .aa-user-nav-identity strong,
    .aa-user-nav-identity small {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .aa-user-nav-identity strong {
        color: #0f172a;
        font-size: 13px;
        font-weight: 950;
    }

    .aa-user-nav-identity small {
        color: #64748b;
        font-size: 11px;
        font-weight: 750;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-identity strong {
        color: #f8fafc;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-identity small {
        color: #cbd5e1;
    }

    .aa-user-nav-panel {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: min(282px, calc(100vw - 28px));
        overflow: hidden;
        border: 1px solid rgba(253, 230, 138, .95);
        border-radius: 22px;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 24px 70px rgba(15, 23, 42, .18);
        opacity: 0;
        pointer-events: none;
        transform: translateY(-6px) scale(.98);
        transform-origin: top right;
        transition: .16s ease;
    }

    .aa-user-nav.is-open .aa-user-nav-panel {
        opacity: 1;
        pointer-events: auto;
        transform: translateY(0) scale(1);
    }

    .aa-user-nav-list {
        display: grid;
        gap: 6px;
        padding: 9px;
    }

    .aa-user-nav-link,
    .aa-user-nav-link-button {
        display: flex;
        width: 100%;
        min-height: 42px;
        align-items: center;
        gap: 10px;
        border: 0;
        border-radius: 15px;
        background: transparent;
        color: #334155;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 850;
        text-decoration: none;
        text-align: left;
        transition: .16s ease;
    }

    .aa-user-nav-link:hover,
    .aa-user-nav-link-button:hover,
    .aa-user-nav-link.is-active {
        background: #f6f0ff;
        color: #7550c4;
    }

    .aa-user-nav-link-button {
        cursor: pointer;
        font-family: inherit;
    }

    .aa-user-nav-premium {
        min-height: 46px;
        border: 1px solid #d9ccf4;
        background: linear-gradient(135deg, #fff9f5 0%, #f1e9ff 52%, #ffffff 100%);
        color: #7550c4;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .7), 0 10px 24px rgba(91, 67, 118, .12);
    }

    .aa-user-nav-premium:hover,
    .aa-user-nav-premium.is-active {
        background: linear-gradient(135deg, #a878f1 0%, #8158d8 100%);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .aa-creator-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        height: 100dvh;
        z-index: 9999;
        display: none;
        place-items: center;
        background: rgba(15, 23, 42, .45);
        padding: 24px 18px;
        overflow-y: auto;
        box-sizing: border-box;
        overscroll-behavior: contain;
    }

    .aa-creator-modal-backdrop.is-open {
        display: grid;
    }

    .aa-creator-modal {
        width: min(460px, 100%);
        max-height: calc(100dvh - 48px);
        overflow-y: auto;
        border: 1px solid #fde68a;
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 30px 90px rgba(15, 23, 42, .24);
        padding: 22px;
        margin: auto;
        box-sizing: border-box;
        transform: none;
    }

    .aa-creator-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
    }

    .aa-creator-modal-kicker {
        color: #8f65df;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .aa-creator-modal-title {
        margin: 5px 0 0;
        color: #0f172a;
        font-size: 22px;
        font-weight: 900;
        line-height: 1.15;
    }

    .aa-creator-modal-close {
        display: inline-flex;
        width: 34px;
        height: 34px;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        font-size: 22px;
        line-height: 1;
    }

    .aa-creator-modal-price {
        margin-top: 16px;
        color: #0f172a;
        font-size: 30px;
        font-weight: 950;
    }

    .aa-creator-modal-desc {
        margin: 12px 0 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.65;
    }

    .aa-creator-modal-note {
        margin: 14px 0 0;
        border: 1px solid #d9ccf4;
        border-radius: 16px;
        background: #f6f0ff;
        color: #7550c4;
        padding: 12px 14px;
        font-size: 13px;
        font-weight: 750;
        line-height: 1.55;
    }

    .aa-creator-modal-list {
        display: grid;
        gap: 9px;
        margin: 18px 0 0;
        padding: 0;
        color: #334155;
        font-size: 13px;
        list-style: none;
    }

    .aa-creator-modal-list li {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        border-radius: 14px;
        background: #f8fafc;
        padding: 10px 12px;
    }

    .aa-creator-modal-list strong {
        color: #0f172a;
        text-align: right;
    }

    .aa-creator-modal-action {
        display: inline-flex;
        width: 100%;
        height: 44px;
        align-items: center;
        justify-content: center;
        margin-top: 20px;
        border-radius: 16px;
        background: #0f172a;
        color: #ffffff;
        font-size: 14px;
        font-weight: 900;
        text-decoration: none;
        transition: .16s ease;
    }

    .aa-creator-modal-action:hover {
        background: #1e293b;
    }

    .aa-creator-modal-action.is-warning {
        background: #f59e0b;
    }

    .aa-creator-modal-action.is-disabled {
        background: #f1f5f9;
        color: #64748b;
        cursor: default;
        pointer-events: none;
    }

    .aa-creator-modal-field {
        display: grid;
        gap: 8px;
        margin-top: 18px;
    }

    .aa-creator-modal-field span {
        color: #0f172a;
        font-size: 13px;
        font-weight: 900;
    }

    .aa-creator-modal-input {
        width: 100%;
        min-height: 44px;
        border: 1px solid #cbd5e1;
        border-radius: 16px;
        background: #ffffff;
        color: #0f172a;
        padding: 0 14px;
        font: inherit;
        font-size: 14px;
        font-weight: 750;
        outline: none;
    }

    .aa-creator-modal-input:focus {
        border-color: #8f65df;
        box-shadow: 0 0 0 4px rgba(143, 101, 223, .16);
    }

    .aa-creator-modal-alert {
        margin-top: 14px;
        border-radius: 14px;
        padding: 10px 12px;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.5;
    }

    .aa-creator-modal-alert.is-error {
        border: 1px solid #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .aa-creator-modal-alert.is-success {
        border: 1px solid #bbf7d0;
        background: #f0fdf4;
        color: #15803d;
    }

    body.aa-creator-modal-open {
        overflow: hidden;
    }

    @media (max-width: 640px) {
        .aa-user-nav-toggle {
            padding: 5px 9px 5px 5px;
        }

        .aa-user-nav-identity {
            display: none;
        }
    }
</style>
<div class="aa-user-nav" data-aa-user-nav>
    <button class="aa-user-nav-toggle" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="<?= esc($menuId, 'attr') ?>" data-aa-user-nav-toggle>
        <?php if ($isLoggedIn): ?>
            <span class="aa-user-nav-avatar" aria-hidden="true"><?= esc($userInitial) ?></span>
            <span class="aa-user-nav-identity">
                <strong><?= esc($userDisplayName) ?></strong>
                <?php if ($userEmail !== ''): ?>
                    <small><?= esc($userEmail) ?></small>
                <?php endif ?>
            </span>
            <?= $icon('chevron') ?>
        <?php else: ?>
            <?= $icon('menu') ?>
            <span>Menu</span>
        <?php endif ?>
    </button>
    <div id="<?= esc($menuId, 'attr') ?>" class="aa-user-nav-panel" role="menu" data-aa-user-nav-panel>
        <div class="aa-user-nav-list">
            <?php if ($isLoggedIn): ?>
                <?php if (! $hidePremium): ?>
                    <a class="<?= esc($linkClass('plans'), 'attr') ?> aa-user-nav-premium" role="menuitem" href="<?= site_url('plans') ?>"><?= $icon('crown') ?><span>Go Premium</span></a>
                <?php endif ?>
                <a class="<?= esc($linkClass('orders'), 'attr') ?>" role="menuitem" href="<?= site_url('orders') ?>"><?= $icon('order') ?><span>Order Saya</span></a>
                <a class="<?= esc($linkClass('dashboard'), 'attr') ?>" role="menuitem" href="<?= site_url('dashboard') ?>"><?= $icon('dashboard') ?><span>Dashboard</span></a>
                <?php if ($isActiveCreator): ?>
                    <a class="<?= esc($linkClass('creator'), 'attr') ?>" role="menuitem" href="<?= esc($creatorMenuUrl, 'attr') ?>"><?= $icon('creator') ?><span><?= esc($creatorMenuLabel) ?></span></a>
                <?php elseif (! $hasActivePublishPlan): ?>
                    <button class="<?= esc($linkClass('creator'), 'attr') ?> aa-user-nav-link-button" type="button" role="menuitem" data-aa-creator-modal-open="<?= esc($creatorModalId, 'attr') ?>"><?= $icon('creator') ?><span>Daftar Creator</span></button>
                <?php endif ?>
                <form action="<?= site_url('logout') ?>" method="post" role="none">
                    <button class="aa-user-nav-link-button" type="submit" role="menuitem"><?= $icon('logout') ?><span>Logout</span></button>
                </form>
            <?php else: ?>
                <?php if (! $hidePremium): ?>
                    <a class="<?= esc($linkClass('plans'), 'attr') ?> aa-user-nav-premium" role="menuitem" href="<?= site_url('plans') ?>"><?= $icon('crown') ?><span>Go Premium</span></a>
                <?php endif ?>
                <a class="<?= esc($linkClass('login'), 'attr') ?>" role="menuitem" href="<?= site_url('login') ?>"><?= $icon('login') ?><span>Login</span></a>
                <a class="<?= esc($linkClass('register'), 'attr') ?>" role="menuitem" href="<?= site_url('register') ?>"><?= $icon('register') ?><span>Daftar</span></a>
            <?php endif ?>
        </div>
    </div>
</div>
<?php if ($isLoggedIn && ! $isActiveCreator && ! $hasActivePublishPlan): ?>
    <div id="<?= esc($creatorModalId, 'attr') ?>" class="aa-creator-modal-backdrop" aria-hidden="true" data-aa-creator-modal>
        <section class="aa-creator-modal" role="dialog" aria-modal="true" aria-labelledby="<?= esc($creatorModalId . 'Title', 'attr') ?>">
            <div class="aa-creator-modal-head">
                <div>
                    <div class="aa-creator-modal-kicker">Creator</div>
                    <h2 id="<?= esc($creatorModalId . 'Title', 'attr') ?>" class="aa-creator-modal-title">Daftar Creator</h2>
                </div>
                <button class="aa-creator-modal-close" type="button" aria-label="Tutup" data-aa-creator-modal-close>⛌</button>
            </div>
            <div class="aa-creator-modal-price">Pengajuan Creator</div>
            <p class="aa-creator-modal-desc">Pendaftaran creator template. Setelah diapprove admin, creator aktif permanen dan bisa submit template untuk review.</p>
            <p class="aa-creator-modal-note">Creator aktif bisa submit template untuk direview admin. Jika template creator dipakai user lain dan memenuhi aturan komisi, pembagian pendapatan adalah 70% untuk creator dan 30% untuk platform.</p>
            <ul class="aa-creator-modal-list">
                <li><span>Akses</span><strong>Creator aktif permanen</strong></li>
                <li><span>Komisi</span><strong>70% creator / 30% platform</strong></li>
                <li><span>Template publik</span><strong>Wajib approve admin</strong></li>
                <li><span>Publish undangan</span><strong>Tidak termasuk</strong></li>
                <li><span>Earnings</span><strong>Dashboard Creator</strong></li>
            </ul>
            <?php if ($creatorModalError !== ''): ?>
                <div class="aa-creator-modal-alert is-error"><?= esc($creatorModalError) ?></div>
            <?php endif ?>
            <?php if ($creatorModalSuccess !== ''): ?>
                <div class="aa-creator-modal-alert is-success"><?= esc($creatorModalSuccess) ?></div>
            <?php endif ?>
            <?php if ($creatorHasPendingApplication): ?>
                <span class="<?= esc($creatorActionClass, 'attr') ?>"><?= esc($creatorActionLabel) ?></span>
            <?php else: ?>
                <form method="post" action="<?= site_url('creator/apply/quick') ?>">
                    <?= csrf_field() ?>
                    <label class="aa-creator-modal-field">
                        <span>Nama Creator</span>
                        <input class="aa-creator-modal-input" type="text" name="display_name" value="<?= esc((string) old('display_name'), 'attr') ?>" minlength="3" maxlength="80" required placeholder="Contoh: Studio Undangan Rara">
                    </label>
                    <button class="<?= esc($creatorActionClass, 'attr') ?>" type="submit"><?= esc($creatorActionLabel) ?></button>
                </form>
            <?php endif ?>
        </section>
    </div>
<?php endif ?>
<script>
    (function() {
        if (window.__aaUserNavDropdownReady) return;
        window.__aaUserNavDropdownReady = true;

        function closeMenu(menu) {
            if (!menu) return;
            menu.classList.remove('is-open');
            const toggle = menu.querySelector('[data-aa-user-nav-toggle]');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        }

        function closeAllMenus(except) {
            document.querySelectorAll('[data-aa-user-nav]').forEach(function(menu) {
                if (menu !== except) closeMenu(menu);
            });
        }

        function openCreatorModal(modal) {
            if (!modal) return;
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('aa-creator-modal-open');
            const input = modal.querySelector('input[name="display_name"]');
            if (input) window.setTimeout(function() { input.focus(); }, 80);
        }

        function closeCreatorModal(modal) {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('aa-creator-modal-open');
        }

        document.addEventListener('click', function(event) {
            const modalOpen = event.target.closest('[data-aa-creator-modal-open]');
            if (modalOpen) {
                const modal = document.getElementById(modalOpen.getAttribute('data-aa-creator-modal-open'));
                closeAllMenus(null);
                openCreatorModal(modal);
                return;
            }

            if (event.target.closest('[data-aa-creator-modal-close]') || event.target.matches('[data-aa-creator-modal]')) {
                const modal = event.target.closest('[data-aa-creator-modal]') || event.target;
                closeCreatorModal(modal);
                return;
            }

            const toggle = event.target.closest('[data-aa-user-nav-toggle]');
            if (toggle) {
                const menu = toggle.closest('[data-aa-user-nav]');
                const open = !menu.classList.contains('is-open');
                closeAllMenus(menu);
                menu.classList.toggle('is-open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                return;
            }

            if (!event.target.closest('[data-aa-user-nav]')) {
                closeAllMenus(null);
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAllMenus(null);
                document.querySelectorAll('[data-aa-creator-modal].is-open').forEach(function(modal) {
                    closeCreatorModal(modal);
                });
            }
        });

        <?php if ($creatorModalError !== '' || $creatorModalSuccess !== ''): ?>
            openCreatorModal(document.getElementById(<?= json_encode($creatorModalId) ?>));
        <?php endif ?>
    })();
</script>
