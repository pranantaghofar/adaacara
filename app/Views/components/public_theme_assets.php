<script>
(function() {
    try {
        var storedTheme = localStorage.getItem('aa-home-theme');
        var systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var theme = storedTheme === 'dark' || storedTheme === 'light' ? storedTheme : (systemDark ? 'dark' : 'light');
        document.documentElement.dataset.aaPublicTheme = theme;
    } catch (error) {
        document.documentElement.dataset.aaPublicTheme = 'light';
    }
})();
</script>
<style>
    .aa-home-theme-toggle {
        display: inline-flex;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 999px;
        background: rgba(255, 255, 255, .82);
        color: #475569;
        cursor: pointer;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .08);
        transition: .18s ease;
    }

    .aa-home-theme-toggle:hover {
        border-color: rgba(143, 101, 223, .42);
        color: #8f65df;
        transform: translateY(-1px);
    }

    .aa-home-theme-toggle svg {
        width: 18px;
        height: 18px;
        stroke-width: 2;
    }

    [data-public-theme-toggle] .aa-home-theme-sun {
        display: none;
    }

    html[data-aa-public-theme="dark"] [data-public-theme-toggle] .aa-home-theme-moon {
        display: none;
    }

    html[data-aa-public-theme="dark"] [data-public-theme-toggle] .aa-home-theme-sun {
        display: block;
    }

    html[data-aa-public-theme="dark"] {
        color-scheme: dark;
    }

    html[data-aa-public-theme="dark"] body.aa-public-theme-page {
        background:
            radial-gradient(circle at 20% -10%, rgba(143, 101, 223, .18), transparent 30rem),
            radial-gradient(circle at 90% 0%, rgba(20, 184, 166, .12), transparent 28rem),
            linear-gradient(180deg, #071018 0%, #0b1220 48%, #070b12 100%) !important;
        color: #e5edf6 !important;
    }

    html[data-aa-public-theme="dark"] body.aa-app-ui.aa-public-theme-page {
        background:
            radial-gradient(circle at 20% -10%, rgba(143, 101, 223, .16), transparent 30rem),
            radial-gradient(circle at 90% 0%, rgba(20, 184, 166, .10), transparent 28rem),
            linear-gradient(rgb(7 11 18 / 82%), rgb(11 18 32 / 88%)),
            url(https://adaacara.com/assets/editor/backgrounds/bg-dash-editor.png) !important;
        background-attachment: fixed !important;
        background-position: center !important;
        background-size: cover !important;
        color: #e5edf6 !important;
    }

    html[data-aa-public-theme="dark"] .aa-public-theme-page .bg-white,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .bg-white\/90,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .bg-white\/80,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .bg-slate-50,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .bg-slate-50\/70 {
        background-color: rgba(15, 23, 42, .9) !important;
    }

    html[data-aa-public-theme="dark"] .aa-public-theme-page .bg-amber-50,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .bg-amber-50\/70,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .bg-violet-50,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .bg-violet-50\/70,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .bg-violet-100 {
        background-color: rgba(91, 67, 118, .28) !important;
    }

    html[data-aa-public-theme="dark"] .aa-public-theme-page .border-slate-100,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .border-slate-200,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .border-slate-300,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .border-amber-100,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .border-amber-200,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .border-violet-100,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .border-violet-200 {
        border-color: rgba(148, 163, 184, .22) !important;
    }

    html[data-aa-public-theme="dark"] .aa-public-theme-page .text-violet-900,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .text-violet-800,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .text-violet-700,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .text-violet-600,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .text-violet-500 {
        color: #d9ccf4 !important;
    }

    html[data-aa-public-theme="dark"] .aa-public-theme-page .text-slate-950,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .text-slate-900,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .text-slate-800 {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-public-theme-page .text-slate-700 {
        color: #d6e0ee !important;
    }

    html[data-aa-public-theme="dark"] .aa-public-theme-page .text-slate-600,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .text-slate-500 {
        color: #a8b5c7 !important;
    }

    html[data-aa-public-theme="dark"] .aa-public-theme-page .shadow-sm,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .shadow,
    html[data-aa-public-theme="dark"] .aa-public-theme-page .shadow-lg {
        box-shadow: 0 18px 48px rgba(0, 0, 0, .28) !important;
    }

    html[data-aa-public-theme="dark"] .aa-public-theme-page input,
    html[data-aa-public-theme="dark"] .aa-public-theme-page select,
    html[data-aa-public-theme="dark"] .aa-public-theme-page textarea {
        background-color: rgba(15, 23, 42, .88) !important;
        border-color: rgba(148, 163, 184, .24) !important;
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-template-detail-nav,
    html[data-aa-public-theme="dark"] .aa-template-card,
    html[data-aa-public-theme="dark"] .aa-template-filter-btn,
    html[data-aa-public-theme="dark"] .aa-template-filter-select,
    html[data-aa-public-theme="dark"] .aa-template-modal-card,
    html[data-aa-public-theme="dark"] .aa-template-modal-preview-light,
    html[data-aa-public-theme="dark"] .aa-template-detail-card,
    html[data-aa-public-theme="dark"] .aa-template-detail-form,
    html[data-aa-public-theme="dark"] .aa-template-detail-preview {
        background-color: rgba(15, 23, 42, .9) !important;
        border-color: rgba(148, 163, 184, .22) !important;
        color: #e5edf6 !important;
    }

    html[data-aa-public-theme="dark"] .aa-template-detail-hero h1,
    html[data-aa-public-theme="dark"] .aa-template-detail-card h2 {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-template-detail-hero p,
    html[data-aa-public-theme="dark"] .aa-template-detail-card p,
    html[data-aa-public-theme="dark"] .aa-template-detail-nav-links a,
    html[data-aa-public-theme="dark"] .aa-template-filter-label {
        color: #cbd5e1 !important;
    }

    html[data-aa-public-theme="dark"] .aa-template-detail-meta span,
    html[data-aa-public-theme="dark"] .aa-template-detail-btn-secondary {
        background-color: rgba(30, 41, 59, .92) !important;
        border-color: rgba(148, 163, 184, .22) !important;
        color: #d6e0ee !important;
    }

    html[data-aa-public-theme="dark"] .aa-home-template-blank-preview,
    html[data-aa-public-theme="dark"] .aa-template-blank-preview {
        background:
            linear-gradient(135deg, rgba(143, 101, 223, .12), transparent 34%),
            linear-gradient(315deg, rgba(20, 184, 166, .10), transparent 42%),
            rgba(15, 23, 42, .92) !important;
    }

    html[data-aa-public-theme="dark"] .aa-home-template-blank-inner,
    html[data-aa-public-theme="dark"] .aa-template-blank-preview-inner {
        border-color: rgba(143, 101, 223, .36) !important;
        background: rgba(30, 41, 59, .72) !important;
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-home-template-blank-inner strong,
    html[data-aa-public-theme="dark"] .aa-template-blank-preview-inner strong {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-home-template-blank-inner span,
    html[data-aa-public-theme="dark"] .aa-template-blank-preview-inner span {
        color: #a8b5c7 !important;
    }

    html[data-aa-public-theme="dark"] .aa-home-template-blank-plus {
        background: #8f65df !important;
        color: #ffffff !important;
        box-shadow: 0 16px 36px rgba(143, 101, 223, .26) !important;
    }

    html[data-aa-public-theme="dark"] .aa-public-logo {
        filter: invert(1) brightness(2.05) contrast(.92);
    }

    html[data-aa-public-theme="dark"] .aa-home-theme-toggle {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .82);
        color: #e2e8f0;
        box-shadow: 0 18px 42px rgba(0, 0, 0, .26);
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-toggle {
        border-color: rgba(148, 163, 184, .28) !important;
        background: rgba(15, 23, 42, .74) !important;
        color: #e2e8f0 !important;
        box-shadow: 0 16px 38px rgba(0, 0, 0, .22) !important;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-toggle:hover,
    html[data-aa-public-theme="dark"] .aa-user-nav.is-open .aa-user-nav-toggle {
        border-color: rgba(143, 101, 223, .55) !important;
        background: rgba(143, 101, 223, .12) !important;
        color: #d9ccf4 !important;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-identity strong {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-identity small {
        color: #cbd5e1 !important;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-panel {
        border-color: rgba(148, 163, 184, .24) !important;
        background: rgba(15, 23, 42, .97) !important;
        box-shadow: 0 26px 70px rgba(0, 0, 0, .34) !important;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-link,
    html[data-aa-public-theme="dark"] .aa-user-nav-link-button {
        color: #cbd5e1 !important;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-link:hover,
    html[data-aa-public-theme="dark"] .aa-user-nav-link-button:hover,
    html[data-aa-public-theme="dark"] .aa-user-nav-link.is-active {
        background: rgba(143, 101, 223, .10) !important;
        color: #d9ccf4 !important;
    }

    html[data-aa-public-theme="dark"] .aa-creator-modal {
        border-color: rgba(148, 163, 184, .24) !important;
        background: rgba(15, 23, 42, .98) !important;
        color: #e5edf6 !important;
    }

    html[data-aa-public-theme="dark"] .aa-creator-modal-title,
    html[data-aa-public-theme="dark"] .aa-creator-modal-price {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-creator-modal-close {
        border-color: rgba(148, 163, 184, .24) !important;
        background: rgba(30, 41, 59, .95) !important;
        color: #e2e8f0 !important;
    }

    html[data-aa-public-theme="dark"] .aa-seo-nav,
    html[data-aa-public-theme="dark"] .aa-guide-nav,
    html[data-aa-public-theme="dark"] .aa-legal-header {
        border-color: rgba(148, 163, 184, .18) !important;
        background: rgba(7, 11, 18, .78) !important;
        box-shadow: 0 18px 46px rgba(0, 0, 0, .18) !important;
    }

    html[data-aa-public-theme="dark"] .aa-seo-card,
    html[data-aa-public-theme="dark"] .aa-seo-template-card,
    html[data-aa-public-theme="dark"] .aa-seo-faq-item,
    html[data-aa-public-theme="dark"] .aa-seo-visual-card,
    html[data-aa-public-theme="dark"] .aa-guide-card,
    html[data-aa-public-theme="dark"] .aa-guide-article,
    html[data-aa-public-theme="dark"] .aa-guide-box,
    html[data-aa-public-theme="dark"] .aa-legal-card,
    html[data-aa-public-theme="dark"] .aa-legal-note {
        border-color: rgba(148, 163, 184, .22) !important;
        background: rgba(15, 23, 42, .9) !important;
        color: #e5edf6 !important;
        box-shadow: 0 22px 56px rgba(0, 0, 0, .24) !important;
    }

    html[data-aa-public-theme="dark"] .aa-seo-hero h1,
    html[data-aa-public-theme="dark"] .aa-seo-section-head h2,
    html[data-aa-public-theme="dark"] .aa-seo-card h2,
    html[data-aa-public-theme="dark"] .aa-seo-card h3,
    html[data-aa-public-theme="dark"] .aa-seo-template-card h3,
    html[data-aa-public-theme="dark"] .aa-seo-faq-item h3,
    html[data-aa-public-theme="dark"] .aa-seo-visual-card strong,
    html[data-aa-public-theme="dark"] .aa-guide-hero h1,
    html[data-aa-public-theme="dark"] .aa-guide-card h2,
    html[data-aa-public-theme="dark"] .aa-guide-article h2,
    html[data-aa-public-theme="dark"] .aa-guide-box h2,
    html[data-aa-public-theme="dark"] .aa-legal-hero h1,
    html[data-aa-public-theme="dark"] .aa-legal-section h2 {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-seo-hero p,
    html[data-aa-public-theme="dark"] .aa-seo-section-head p,
    html[data-aa-public-theme="dark"] .aa-seo-card p,
    html[data-aa-public-theme="dark"] .aa-seo-template-card p,
    html[data-aa-public-theme="dark"] .aa-seo-faq-item p,
    html[data-aa-public-theme="dark"] .aa-guide-hero p,
    html[data-aa-public-theme="dark"] .aa-guide-card p,
    html[data-aa-public-theme="dark"] .aa-guide-article p,
    html[data-aa-public-theme="dark"] .aa-guide-box p,
    html[data-aa-public-theme="dark"] .aa-legal-hero p,
    html[data-aa-public-theme="dark"] .aa-legal-section p {
        color: #a8b5c7 !important;
    }

    html[data-aa-public-theme="dark"] .aa-seo-nav-links a,
    html[data-aa-public-theme="dark"] .aa-guide-nav-links a,
    html[data-aa-public-theme="dark"] .aa-legal-nav-links a,
    html[data-aa-public-theme="dark"] .aa-guide-back,
    html[data-aa-public-theme="dark"] .aa-guide-related a {
        color: #cbd5e1 !important;
    }

    html[data-aa-public-theme="dark"] .aa-seo-nav-links a:hover,
    html[data-aa-public-theme="dark"] .aa-guide-nav-links a:hover,
    html[data-aa-public-theme="dark"] .aa-legal-nav-links a:hover,
    html[data-aa-public-theme="dark"] .aa-guide-back:hover,
    html[data-aa-public-theme="dark"] .aa-guide-related a:hover {
        background: rgba(143, 101, 223, .10) !important;
        color: #d9ccf4 !important;
    }

    html[data-aa-public-theme="dark"] .aa-seo-btn-secondary,
    html[data-aa-public-theme="dark"] .aa-guide-btn,
    html[data-aa-public-theme="dark"] .aa-guide-meta span,
    html[data-aa-public-theme="dark"] .aa-guide-card-meta span,
    html[data-aa-public-theme="dark"] .aa-guide-categories span,
    html[data-aa-public-theme="dark"] .aa-seo-keywords span,
    html[data-aa-public-theme="dark"] .aa-legal-updated {
        border-color: rgba(148, 163, 184, .22) !important;
        background: rgba(30, 41, 59, .88) !important;
        color: #d6e0ee !important;
    }

    html[data-aa-public-theme="dark"] .aa-guide-eyebrow,
    html[data-aa-public-theme="dark"] .aa-legal-eyebrow,
    html[data-aa-public-theme="dark"] .aa-seo-eyebrow,
    html[data-aa-public-theme="dark"] .aa-guide-card small,
    html[data-aa-public-theme="dark"] .aa-guide-related small {
        color: #d9ccf4 !important;
    }

    html[data-aa-public-theme="dark"] .aa-guide-checklist,
    html[data-aa-public-theme="dark"] .aa-seo-template-thumb {
        border-color: rgba(148, 163, 184, .18) !important;
        background: rgba(7, 11, 18, .42) !important;
    }

    html[data-aa-public-theme="dark"] .aa-seo-template-copy a,
    html[data-aa-public-theme="dark"] .aa-guide-cta {
        color: #d9ccf4 !important;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var buttons = document.querySelectorAll('[data-public-theme-toggle]');

    function getTheme() {
        return document.documentElement.dataset.aaPublicTheme === 'dark' ? 'dark' : 'light';
    }

    function syncButtons() {
        var theme = getTheme();
        var nextLabel = theme === 'dark' ? 'Gunakan tema terang' : 'Gunakan tema gelap';
        buttons.forEach(function(button) {
            button.setAttribute('aria-label', nextLabel);
            button.setAttribute('title', nextLabel);
            button.dataset.publicThemeCurrent = theme;
        });
    }

    function setTheme(theme) {
        var nextTheme = theme === 'dark' ? 'dark' : 'light';
        document.documentElement.dataset.aaPublicTheme = nextTheme;
        try {
            localStorage.setItem('aa-home-theme', nextTheme);
        } catch (error) {}
        syncButtons();
    }

    buttons.forEach(function(button) {
        button.addEventListener('click', function() {
            setTheme(getTheme() === 'dark' ? 'light' : 'dark');
        });
    });

    syncButtons();
});
</script>
