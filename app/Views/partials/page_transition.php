<style>
.ki-page-transition {
    position: fixed;
    inset: 0;
    z-index: 999999;
    display: grid;
    place-items: center;
    background:
        radial-gradient(circle at 50% 45%, rgba(103, 232, 249, .24), transparent 34%),
        radial-gradient(circle at 20% 20%, rgba(99, 102, 241, .22), transparent 30%),
        radial-gradient(circle at 80% 80%, rgba(168, 85, 247, .18), transparent 32%),
        rgba(2, 6, 23, .88);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity .35s ease, visibility .35s ease;
}

.ki-page-transition.is-active {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

.ki-page-transition::before {
    content: "";
    position: absolute;
    width: 320px;
    height: 320px;
    border-radius: 999px;
    background: rgba(103, 232, 249, .20);
    filter: blur(55px);
    animation: kiGlowMove 2.2s ease-in-out infinite;
}

.ki-page-transition-box {
    position: relative;
    width: 96px;
    height: 96px;
    border-radius: 32px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .18);
    background: rgba(255, 255, 255, .11);
    box-shadow:
        0 25px 80px rgba(0, 0, 0, .45),
        0 0 60px rgba(103, 232, 249, .18);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    transform: translateY(14px) scale(.94);
    opacity: 0;
    transition: .35s ease;
    overflow: hidden;
}

.ki-page-transition.is-active .ki-page-transition-box {
    transform: translateY(0) scale(1);
    opacity: 1;
    animation: kiTransitionFloat 1.4s ease-in-out infinite;
}

.ki-page-transition-box::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, .18), transparent 52%);
    pointer-events: none;
}

.ki-page-transition-loader {
    position: relative;
    width: 38px;
    height: 38px;
    border-radius: 999px;
    border: 3px solid rgba(255, 255, 255, .22);
    border-top-color: #67e8f9;
    border-right-color: rgba(216, 180, 254, .85);
    animation: kiTransitionSpin .75s linear infinite;
    box-shadow: 0 0 28px rgba(103, 232, 249, .28);
}

.ki-page-transition-text {
    position: absolute;
    bottom: calc(50% - 88px);
    left: 50%;
    transform: translateX(-50%) translateY(10px);
    opacity: 0;
    color: #cbd5e1;
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    transition: .35s ease .08s;
    white-space: nowrap;
}

.ki-page-transition.is-active .ki-page-transition-text {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

body.ki-page-enter main,
body.ki-page-enter header {
    animation: kiPageEnter .55s ease both;
}

body.ki-page-leave main,
body.ki-page-leave header {
    animation: kiPageLeave .32s ease both;
}

@keyframes kiPageEnter {
    from {
        opacity: 0;
        filter: blur(10px);
        transform: translateY(12px) scale(.985);
    }

    to {
        opacity: 1;
        filter: blur(0);
        transform: translateY(0) scale(1);
    }
}

@keyframes kiPageLeave {
    to {
        opacity: 0;
        filter: blur(10px);
        transform: translateY(-10px) scale(.985);
    }
}

@keyframes kiTransitionSpin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes kiTransitionFloat {

    0%,
    100% {
        transform: translateY(0) scale(1);
    }

    50% {
        transform: translateY(-8px) scale(1.03);
    }
}

@keyframes kiGlowMove {

    0%,
    100% {
        transform: translate(-20px, -10px) scale(1);
        opacity: .75;
    }

    50% {
        transform: translate(24px, 18px) scale(1.08);
        opacity: 1;
    }
}
</style>

<div class="ki-page-transition" id="kiPageTransition">
    <div class="ki-page-transition-box">
        <div class="ki-page-transition-loader"></div>
    </div>

    <div class="ki-page-transition-text">
        Loading
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const transition = document.getElementById('kiPageTransition');

    if (!transition) return;

    document.body.classList.add('ki-page-enter');

    function showTransition() {
        document.body.classList.add('ki-page-leave');
        transition.classList.add('is-active');
    }

    function hideTransition() {
        document.body.classList.remove('ki-page-leave');
        transition.classList.remove('is-active');
    }

    function isSpecialClick(e) {
        return e.ctrlKey || e.metaKey || e.shiftKey || e.altKey || e.button !== 0;
    }

    document.addEventListener('click', function(e) {
        const link = e.target.closest('a[href]');

        if (!link) return;
        if (isSpecialClick(e)) return;

        const href = link.getAttribute('href');
        const target = link.getAttribute('target');

        if (
            !href ||
            href === '#' ||
            href.startsWith('#') ||
            href.startsWith('javascript:') ||
            href.startsWith('mailto:') ||
            href.startsWith('tel:') ||
            target === '_blank' ||
            link.hasAttribute('download') ||
            link.classList.contains('no-transition')
        ) {
            return;
        }

        showTransition();
    }, true);

    document.addEventListener('submit', function(e) {
        const form = e.target;

        if (!form || form.tagName.toLowerCase() !== 'form') return;
        if (form.classList.contains('no-transition')) return;
        if (!form.checkValidity()) return;

        showTransition();
    }, true);

    window.addEventListener('pageshow', function() {
        hideTransition();
    });
});
</script>