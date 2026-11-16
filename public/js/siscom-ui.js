(function () {
    'use strict';

    const storageKey = 'siscom-theme';
    const body = document.body;
    const toggle = document.querySelector('[data-theme-toggle]');
    const icon = document.querySelector('[data-theme-icon]');

    function setTheme(theme) {
        const dark = theme === 'dark';
        body.classList.toggle('dark-mode', dark);

        if (icon) {
            icon.classList.toggle('fa-moon', !dark);
            icon.classList.toggle('fa-sun', dark);
        }

        if (toggle) {
            toggle.setAttribute('aria-label', dark ? 'Aktifkan tema terang' : 'Aktifkan tema gelap');
            toggle.setAttribute('title', dark ? 'Tema terang' : 'Tema gelap');
        }
    }

    setTheme(localStorage.getItem(storageKey) || 'light');

    if (toggle) {
        toggle.addEventListener('click', function () {
            const nextTheme = body.classList.contains('dark-mode') ? 'light' : 'dark';
            localStorage.setItem(storageKey, nextTheme);
            setTheme(nextTheme);
        });
    }

    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (!form.checkValidity() || form.dataset.noSubmitLock !== undefined) {
                return;
            }

            sessionStorage.setItem('siscom-scroll:' + location.pathname, String(window.scrollY));

            const button = form.querySelector('button[type="submit"]');
            if (button && !button.disabled) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...';
            }
        });
    });

    const scrollKey = 'siscom-scroll:' + location.pathname;
    const storedScroll = sessionStorage.getItem(scrollKey);
    if (storedScroll !== null) {
        sessionStorage.removeItem(scrollKey);
        window.requestAnimationFrame(function () {
            window.scrollTo({ top: Number(storedScroll), behavior: 'auto' });
        });
    }

    const alertMessages = new Set();
    document.querySelectorAll('.alert').forEach(function (alert) {
        const message = alert.textContent.replace('×', '').replace(/\s+/g, ' ').trim();

        if (message && alertMessages.has(message)) {
            alert.remove();
            return;
        }

        alertMessages.add(message);
    });

    window.setTimeout(function () {
        if (window.jQuery) {
            window.jQuery('.sis-alert.alert-success, .sis-alert.alert-info').alert('close');
        }
    }, 6000);
})();
