<style>
    .aa-modern-toast-stack {
        position: fixed;
        top: 18px;
        right: 18px;
        z-index: 999999;
        display: grid;
        gap: 10px;
        width: min(360px, calc(100vw - 32px));
        pointer-events: none;
    }
    .aa-modern-toast {
        pointer-events: auto;
        display: grid;
        grid-template-columns: 38px 1fr auto;
        gap: 12px;
        align-items: start;
        padding: 14px;
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 20px;
        background: rgba(255, 255, 255, .94);
        box-shadow: 0 22px 70px rgba(15, 23, 42, .16);
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        color: #0f172a;
        transform: translateY(-8px);
        opacity: 0;
        transition: opacity .18s ease, transform .18s ease;
    }
    .aa-modern-toast.is-visible {
        transform: translateY(0);
        opacity: 1;
    }
    .aa-modern-toast-icon {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border-radius: 16px;
        background: #ecfdf5;
        color: #047857;
        font-weight: 900;
    }
    .aa-modern-toast[data-tone="error"] .aa-modern-toast-icon {
        background: #fff1f2;
        color: #be123c;
    }
    .aa-modern-toast[data-tone="warning"] .aa-modern-toast-icon {
        background: #fffbeb;
        color: #b45309;
    }
    .aa-modern-toast-title {
        margin: 0;
        font: 800 13px/1.2 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .aa-modern-toast-message {
        margin: 3px 0 0;
        color: #64748b;
        font: 700 12px/1.45 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .aa-modern-toast-close,
    .aa-modern-confirm-close {
        border: 0;
        background: transparent;
        color: #64748b;
        cursor: pointer;
        font: 900 18px/1 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .aa-modern-confirm[hidden] {
        display: none !important;
    }
    .aa-modern-confirm {
        position: fixed;
        inset: 0;
        z-index: 999998;
        display: grid;
        place-items: center;
        padding: 18px;
    }
    .aa-modern-confirm-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .32);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }
    .aa-modern-confirm-card {
        position: relative;
        width: min(420px, 100%);
        border: 1px solid rgba(255, 255, 255, .48);
        border-radius: 24px;
        background: rgba(255, 255, 255, .95);
        box-shadow: 0 24px 90px rgba(15, 23, 42, .22);
        padding: 20px;
        color: #0f172a;
    }
    .aa-modern-confirm-head {
        display: flex;
        align-items: start;
        justify-content: space-between;
        gap: 12px;
    }
    .aa-modern-confirm-title {
        margin: 0;
        font: 900 18px/1.2 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .aa-modern-confirm-message {
        margin: 10px 0 0;
        color: #64748b;
        font: 700 14px/1.55 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .aa-modern-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
    .aa-modern-confirm-btn {
        min-height: 42px;
        border-radius: 16px;
        border: 1px solid rgba(15, 23, 42, .1);
        padding: 0 16px;
        cursor: pointer;
        font: 900 13px/1 system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }
    .aa-modern-confirm-cancel {
        background: #fff;
        color: #334155;
    }
    .aa-modern-confirm-ok {
        border-color: transparent;
        background: #047857;
        color: #fff;
        box-shadow: 0 12px 30px rgba(4, 120, 87, .22);
    }
    .aa-modern-confirm-ok.is-danger {
        background: #e11d48;
        box-shadow: 0 12px 30px rgba(225, 29, 72, .22);
    }
</style>
<script>
(function () {
    if (window.aaModernAlertsReady) return;
    window.aaModernAlertsReady = true;

    function ensureToastStack() {
        let stack = document.querySelector('.aa-modern-toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'aa-modern-toast-stack';
            document.body.appendChild(stack);
        }
        return stack;
    }

    function iconForTone(tone) {
        if (tone === 'error') return '!';
        if (tone === 'warning') return 'i';
        return '✓';
    }

    window.aaToast = function (message, tone, title) {
        const cleanMessage = String(message || '');
        const cleanTone = tone || (/gagal|error|tidak|invalid|kesalahan/i.test(cleanMessage) ? 'error' : 'success');
        const toast = document.createElement('div');
        toast.className = 'aa-modern-toast';
        toast.dataset.tone = cleanTone;
        toast.innerHTML = '<span class="aa-modern-toast-icon">' + iconForTone(cleanTone) + '</span>' +
            '<div><p class="aa-modern-toast-title"></p><p class="aa-modern-toast-message"></p></div>' +
            '<button class="aa-modern-toast-close" type="button" aria-label="Tutup">⛌</button>';
        toast.querySelector('.aa-modern-toast-title').textContent = title || (cleanTone === 'error' ? 'Terjadi masalah' : 'Berhasil');
        toast.querySelector('.aa-modern-toast-message').textContent = cleanMessage;
        toast.querySelector('.aa-modern-toast-close').addEventListener('click', function () {
            toast.remove();
        });
        ensureToastStack().appendChild(toast);
        requestAnimationFrame(function () { toast.classList.add('is-visible'); });
        window.setTimeout(function () {
            toast.classList.remove('is-visible');
            window.setTimeout(function () { toast.remove(); }, 220);
        }, cleanTone === 'error' ? 5200 : 3600);
    };

    window.alert = function (message) {
        window.aaToast(message);
    };

    window.aaConfirm = function (message, options) {
        const settings = Object.assign({
            title: 'Konfirmasi',
            okText: 'Lanjutkan',
            cancelText: 'Batal',
            danger: true
        }, options || {});

        return new Promise(function (resolve) {
            let modal = document.querySelector('.aa-modern-confirm');
            if (!modal) {
                modal = document.createElement('div');
                modal.className = 'aa-modern-confirm';
                modal.hidden = true;
                modal.innerHTML = '<div class="aa-modern-confirm-backdrop"></div>' +
                    '<div class="aa-modern-confirm-card" role="dialog" aria-modal="true">' +
                    '<div class="aa-modern-confirm-head"><div><h2 class="aa-modern-confirm-title"></h2><p class="aa-modern-confirm-message"></p></div>' +
                    '<button class="aa-modern-confirm-close" type="button" aria-label="Tutup">⛌</button></div>' +
                    '<div class="aa-modern-confirm-actions"><button class="aa-modern-confirm-btn aa-modern-confirm-cancel" type="button"></button>' +
                    '<button class="aa-modern-confirm-btn aa-modern-confirm-ok" type="button"></button></div></div>';
                document.body.appendChild(modal);
            }

            const close = function (value) {
                modal.hidden = true;
                resolve(value);
            };

            modal.querySelector('.aa-modern-confirm-title').textContent = settings.title;
            modal.querySelector('.aa-modern-confirm-message').textContent = String(message || '');
            modal.querySelector('.aa-modern-confirm-cancel').textContent = settings.cancelText;
            modal.querySelector('.aa-modern-confirm-ok').textContent = settings.okText;
            modal.querySelector('.aa-modern-confirm-ok').classList.toggle('is-danger', !!settings.danger);
            modal.hidden = false;

            modal.querySelector('.aa-modern-confirm-cancel').onclick = function () { close(false); };
            modal.querySelector('.aa-modern-confirm-close').onclick = function () { close(false); };
            modal.querySelector('.aa-modern-confirm-backdrop').onclick = function () { close(false); };
            modal.querySelector('.aa-modern-confirm-ok').onclick = function () { close(true); };
        });
    };

    window.aaConfirmSubmit = function (event, message, options) {
        const form = event.currentTarget;
        if (form.dataset.aaConfirmed === '1') {
            delete form.dataset.aaConfirmed;
            return true;
        }
        event.preventDefault();
        window.aaConfirm(message, options).then(function (ok) {
            if (!ok) return;
            form.dataset.aaConfirmed = '1';
            form.submit();
        });
        return false;
    };
})();
</script>
