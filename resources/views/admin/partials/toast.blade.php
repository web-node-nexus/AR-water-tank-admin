@php
    $toastMessages = [];
    if (session('success')) $toastMessages[] = ['type' => 'success', 'message' => session('success')];
    if (session('error'))   $toastMessages[] = ['type' => 'error',   'message' => session('error')];
    if (session('warning')) $toastMessages[] = ['type' => 'warning', 'message' => session('warning')];
    if (session('info'))    $toastMessages[] = ['type' => 'info',    'message' => session('info')];
    if ($errors->any()) {
        foreach ($errors->all() as $err) {
            $toastMessages[] = ['type' => 'error', 'message' => $err];
        }
    }
@endphp

<div id="ar-toast-root"></div>

<style>
    #ar-toast-root {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 12px;
        width: 360px;
        max-width: calc(100vw - 40px);
        pointer-events: none;
    }
    .ar-toast {
        pointer-events: auto;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: #ffffff;
        border-radius: 14px;
        padding: 14px 14px 14px 16px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.16), 0 2px 6px rgba(15, 23, 42, 0.08);
        border: 1px solid #eef2f7;
        position: relative;
        overflow: hidden;
        transform: translateX(120%);
        opacity: 0;
        transition: transform .38s cubic-bezier(.22,1,.36,1), opacity .38s ease;
    }
    .ar-toast.show { transform: translateX(0); opacity: 1; }
    .ar-toast.hide { transform: translateX(120%); opacity: 0; }
    .ar-toast__accent {
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 4px;
        border-radius: 4px;
    }
    .ar-toast__icon {
        flex-shrink: 0;
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
    }
    .ar-toast__icon svg { width: 20px; height: 20px; }
    .ar-toast__body { flex: 1; min-width: 0; padding-top: 1px; }
    .ar-toast__title { font-size: 13.5px; font-weight: 700; color: #0f172a; line-height: 1.3; }
    .ar-toast__msg { font-size: 13px; color: #64748b; margin-top: 2px; line-height: 1.4; word-break: break-word; }
    .ar-toast__close {
        flex-shrink: 0;
        background: transparent; border: 0; cursor: pointer;
        color: #94a3b8; padding: 2px; border-radius: 6px; line-height: 0;
        transition: background .15s, color .15s;
    }
    .ar-toast__close:hover { background: #f1f5f9; color: #475569; }
    .ar-toast__progress {
        position: absolute;
        left: 0; bottom: 0; height: 3px;
        width: 100%;
        transform-origin: left;
        animation: ar-toast-progress linear forwards;
    }
    @keyframes ar-toast-progress { from { transform: scaleX(1); } to { transform: scaleX(0); } }

    .ar-t-success .ar-toast__accent, .ar-t-success .ar-toast__progress { background: #10b981; }
    .ar-t-success .ar-toast__icon { background: #d1fae5; color: #059669; }
    .ar-t-error   .ar-toast__accent, .ar-t-error   .ar-toast__progress { background: #ef4444; }
    .ar-t-error   .ar-toast__icon { background: #fee2e2; color: #dc2626; }
    .ar-t-warning .ar-toast__accent, .ar-t-warning .ar-toast__progress { background: #f59e0b; }
    .ar-t-warning .ar-toast__icon { background: #fef3c7; color: #d97706; }
    .ar-t-info    .ar-toast__accent, .ar-t-info    .ar-toast__progress { background: #0891b2; }
    .ar-t-info    .ar-toast__icon { background: #e0f2fe; color: #0e7490; }
</style>

<script>
(function () {
    const ICONS = {
        success: '<svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
        error:   '<svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>',
        warning: '<svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>',
        info:    '<svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };
    const TITLES = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Info' };
    const DURATION = 4500;

    const root = document.getElementById('ar-toast-root');
    if (!root) return;

    window.arToast = function (type, message, title) {
        type = ICONS[type] ? type : 'info';
        const el = document.createElement('div');
        el.className = 'ar-toast ar-t-' + type;
        el.innerHTML =
            '<span class="ar-toast__accent"></span>' +
            '<span class="ar-toast__icon">' + ICONS[type] + '</span>' +
            '<div class="ar-toast__body">' +
                '<div class="ar-toast__title">' + (title || TITLES[type]) + '</div>' +
                '<div class="ar-toast__msg"></div>' +
            '</div>' +
            '<button type="button" class="ar-toast__close" aria-label="Close">' +
                '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' +
            '</button>' +
            '<span class="ar-toast__progress" style="animation-duration:' + DURATION + 'ms"></span>';
        el.querySelector('.ar-toast__msg').textContent = message;

        root.appendChild(el);
        requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add('show')));

        let timer;
        const dismiss = () => {
            clearTimeout(timer);
            el.classList.remove('show');
            el.classList.add('hide');
            el.addEventListener('transitionend', () => el.remove(), { once: true });
            setTimeout(() => el.remove(), 500);
        };
        el.querySelector('.ar-toast__close').addEventListener('click', dismiss);
        timer = setTimeout(dismiss, DURATION);
    };

    const initial = @json($toastMessages);
    initial.forEach((t, i) => setTimeout(() => window.arToast(t.type, t.message), i * 180));
})();
</script>
