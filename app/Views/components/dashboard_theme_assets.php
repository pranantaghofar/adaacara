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
        background: rgba(255, 255, 255, .84);
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

    html[data-aa-public-theme="dark"] body.aa-dashboard-theme-page {
        background:
            radial-gradient(circle at 18% -8%, rgba(143, 101, 223, .16), transparent 30rem),
            radial-gradient(circle at 92% 0%, rgba(20, 184, 166, .10), transparent 28rem),
            linear-gradient(rgb(7 11 18 / 86%), rgb(11 18 32 / 90%)),
            url(https://adaacara.com/assets/editor/backgrounds/bg-dash-editor.png) !important;
        background-attachment: scroll !important;
        background-position: center !important;
        background-size: 900px auto !important;
        color: #e5edf6 !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page header.sticky {
        border-color: rgba(148, 163, 184, .18) !important;
        background: rgba(7, 11, 18, .92) !important;
        box-shadow: 0 10px 26px rgba(0, 0, 0, .16) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        backdrop-filter: blur(8px) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page img[src*="adaacara-logo"] {
        filter: invert(1) brightness(2.05) contrast(.92);
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-white,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-white\/95,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-white\/90,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-white\/85,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-white\/82,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-white\/80,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-white\/75,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-white\/70,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-slate-50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-slate-50\/70 {
        background-color: rgba(15, 23, 42, .9) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-amber-50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-amber-50\/70,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-amber-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-amber-100\/85 {
        background-color: rgba(143, 101, 223, .16) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-emerald-50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-emerald-50\/70,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-emerald-50\/60 {
        background-color: rgba(6, 78, 59, .30) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-rose-50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-rose-50\/70,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-rose-100 {
        background-color: rgba(127, 29, 29, .28) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-sky-50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-sky-50\/70 {
        background-color: rgba(12, 74, 110, .30) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-blue-50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-blue-100 {
        background-color: rgba(30, 64, 175, .24) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-violet-50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-violet-50\/70,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-violet-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-violet-100\/60,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-indigo-50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-indigo-100 {
        background-color: rgba(79, 70, 229, .22) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-orange-50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-orange-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-yellow-50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-yellow-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-lime-50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-lime-100 {
        background-color: rgba(91, 67, 118, .18) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-\[\#fbf7ea\]\/88,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-\[\#faf7ea\]\/80,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-\[\#faf7ea\]\/50,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-\[\#f4efe0\],
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-\[\#eef1e6\] {
        background-color: rgba(30, 41, 59, .86) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-slate-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-slate-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-slate-200\/70,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-slate-300,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-amber-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-amber-100\/80,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-amber-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-violet-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-violet-100\/80,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-violet-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-emerald-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-emerald-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-emerald-900\/10,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-rose-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-rose-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-blue-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-orange-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-orange-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-\[\#e8dfcc\],
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-\[\#d9cfb8\],
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .border-dashed {
        border-color: rgba(148, 163, 184, .24) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .divide-slate-200 > :not([hidden]) ~ :not([hidden]) {
        border-color: rgba(148, 163, 184, .20) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .ring-slate-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .ring-amber-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .ring-amber-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .ring-violet-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .ring-violet-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .ring-emerald-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .ring-emerald-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .ring-rose-100,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .ring-rose-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .ring-blue-200,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .ring-\[\#e8dfcc\] {
        --tw-ring-color: rgba(148, 163, 184, .24) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-slate-950,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-slate-900,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-slate-800 {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-slate-700 {
        color: #d6e0ee !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-slate-600,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-slate-500,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-slate-400 {
        color: #a8b5c7 !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-amber-900,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-amber-800,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-amber-700,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-orange-700 {
        color: #d9ccf4 !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-emerald-900,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-emerald-950,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-emerald-800,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-emerald-700,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-emerald-600,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-emerald-500,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-teal-700,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-teal-600 {
        color: #86efac !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-rose-900,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-rose-800,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-rose-700,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-rose-600 {
        color: #fda4af !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-blue-700,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-sky-900,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-sky-800,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-sky-700,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-indigo-500 {
        color: #93c5fd !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-violet-900,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-violet-800,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-violet-700,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-violet-600,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-violet-500 {
        color: #d9ccf4 !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-orange-900,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-orange-800,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-orange-500,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-yellow-700,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-lime-800 {
        color: #c4b5fd !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-\[\#061f14\] {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-white .bg-white,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-white .hover\:bg-emerald-50:hover {
        background-color: rgba(255, 255, 255, .94) !important;
        color: #064e3b !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-white .bg-white *,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .text-white .hover\:bg-emerald-50:hover * {
        color: #064e3b !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-gradient-to-br.text-white .text-emerald-950,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .bg-gradient-to-br.text-white .text-emerald-900 {
        color: #064e3b !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page input,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page select,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page textarea {
        background-color: rgba(15, 23, 42, .88) !important;
        border-color: rgba(148, 163, 184, .28) !important;
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page input::placeholder,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page textarea::placeholder {
        color: #94a3b8 !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page table thead,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .hover\:bg-slate-50\/70:hover,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .hover\:bg-slate-50:hover {
        background-color: rgba(30, 41, 59, .82) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .shadow-sm,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .shadow,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .shadow-lg,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .shadow-xl,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .shadow-2xl {
        box-shadow: 0 10px 28px rgba(0, 0, 0, .20) !important;
    }

    html[data-aa-public-theme="dark"] body.aa-dashboard-home-page {
        background: linear-gradient(180deg, #071018 0%, #0b1220 52%, #070b12 100%) !important;
        background-attachment: scroll !important;
        background-position: center !important;
        background-size: auto !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-home-page header.sticky,
    html[data-aa-public-theme="dark"] .aa-dashboard-home-page .backdrop-blur,
    html[data-aa-public-theme="dark"] .aa-dashboard-home-page .backdrop-blur-xl {
        -webkit-backdrop-filter: none !important;
        backdrop-filter: none !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-home-page .shadow-sm,
    html[data-aa-public-theme="dark"] .aa-dashboard-home-page .shadow,
    html[data-aa-public-theme="dark"] .aa-dashboard-home-page .shadow-lg,
    html[data-aa-public-theme="dark"] .aa-dashboard-home-page .shadow-xl,
    html[data-aa-public-theme="dark"] .aa-dashboard-home-page .shadow-2xl {
        box-shadow: 0 6px 16px rgba(0, 0, 0, .14) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-home-page [data-dashboard-card] {
        contain: content;
        box-shadow: none !important;
        transform: none !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-home-page [data-dashboard-card]:hover {
        box-shadow: 0 8px 22px rgba(0, 0, 0, .18) !important;
        transform: none !important;
    }

    html[data-aa-public-theme="dark"] .aa-home-theme-toggle {
        border-color: rgba(148, 163, 184, .24);
        background: rgba(15, 23, 42, .84);
        color: #e2e8f0;
        box-shadow: 0 10px 26px rgba(0, 0, 0, .20);
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-toggle {
        border-color: rgba(148, 163, 184, .28) !important;
        background: rgba(15, 23, 42, .74) !important;
        color: #e2e8f0 !important;
        box-shadow: 0 10px 26px rgba(0, 0, 0, .18) !important;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-identity strong {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-identity small {
        color: #a8b5c7 !important;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-toggle:hover,
    html[data-aa-public-theme="dark"] .aa-user-nav.is-open .aa-user-nav-toggle {
        border-color: rgba(143, 101, 223, .55) !important;
        background: rgba(143, 101, 223, .12) !important;
        color: #d9ccf4 !important;
    }

    html[data-aa-public-theme="dark"] .aa-user-nav-panel,
    html[data-aa-public-theme="dark"] .aa-creator-modal {
        border-color: rgba(148, 163, 184, .24) !important;
        background: rgba(15, 23, 42, .97) !important;
        color: #e5edf6 !important;
        box-shadow: 0 18px 46px rgba(0, 0, 0, .28) !important;
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

    html[data-aa-public-theme="dark"] .aa-creator-modal-title,
    html[data-aa-public-theme="dark"] .aa-creator-modal-price {
        color: #f8fafc !important;
    }

    html[data-aa-public-theme="dark"] .aa-creator-modal-close {
        border-color: rgba(148, 163, 184, .24) !important;
        background: rgba(30, 41, 59, .95) !important;
        color: #e2e8f0 !important;
    }

    html[data-aa-public-theme="dark"] .aa-site-footer {
        border-top-color: rgba(148, 163, 184, .18) !important;
        background:
            radial-gradient(circle at 15% 0%, rgba(143, 101, 223, .16), transparent 28rem),
            linear-gradient(180deg, #0b1220 0%, #070b12 100%) !important;
        color: #a8b5c7 !important;
    }

    html[data-aa-public-theme="dark"] .aa-site-footer-logo {
        filter: invert(1) brightness(2.05) contrast(.92);
    }

    html[data-aa-public-theme="dark"] .aa-site-footer-desc,
    html[data-aa-public-theme="dark"] .aa-site-footer-list a {
        color: #cbd5e1 !important;
    }

    html[data-aa-public-theme="dark"] .aa-site-footer-contact a,
    html[data-aa-public-theme="dark"] .aa-site-footer-contact span,
    html[data-aa-public-theme="dark"] .aa-site-footer-bottom {
        color: #94a3b8 !important;
    }

    html[data-aa-public-theme="dark"] .aa-site-footer-bottom {
        border-top-color: rgba(148, 163, 184, .18) !important;
    }

    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .wa-topbar,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .wa-card,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .wa-modal-card,
    html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .wa-preview {
        -webkit-backdrop-filter: blur(8px) !important;
        backdrop-filter: blur(8px) !important;
    }

    @media (max-width: 820px) {
        html[data-aa-public-theme="dark"] .aa-dashboard-theme-page header.sticky,
        html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .wa-topbar,
        html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .wa-card,
        html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .wa-modal-card,
        html[data-aa-public-theme="dark"] .aa-dashboard-theme-page .wa-preview {
            -webkit-backdrop-filter: none !important;
            backdrop-filter: none !important;
        }
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

    function syncLocalThemeTargets() {
        var theme = getTheme();
        document.querySelectorAll('[data-dashboard-theme-sync]').forEach(function(target) {
            target.dataset.theme = theme;
        });
    }

    function setTheme(theme) {
        var nextTheme = theme === 'dark' ? 'dark' : 'light';
        document.documentElement.dataset.aaPublicTheme = nextTheme;
        try {
            localStorage.setItem('aa-home-theme', nextTheme);
        } catch (error) {}
        syncButtons();
        syncLocalThemeTargets();
        try {
            document.dispatchEvent(new CustomEvent('adaacara:dashboard-theme-change', {
                detail: { theme: nextTheme }
            }));
        } catch (error) {}
    }

    buttons.forEach(function(button) {
        button.addEventListener('click', function() {
            setTheme(getTheme() === 'dark' ? 'light' : 'dark');
        });
    });

    syncButtons();
    syncLocalThemeTargets();
});
</script>
