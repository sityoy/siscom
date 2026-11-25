document.querySelectorAll('input[type="password"]').forEach(input => {
    if (input.parentElement.querySelector('[data-password-toggle]')) return;

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'btn btn-sm btn-outline-secondary mt-1 d-inline-flex align-items-center gap-1';
    if (input.id) toggle.setAttribute('aria-controls', input.id);

    const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    icon.setAttribute('aria-hidden', 'true');
    icon.setAttribute('viewBox', '0 0 24 24');
    icon.setAttribute('width', '16');
    icon.setAttribute('height', '16');
    icon.setAttribute('fill', 'none');
    icon.setAttribute('stroke', 'currentColor');
    icon.setAttribute('stroke-width', '1.75');
    const path = document.createElementNS(icon.namespaceURI, 'path');
    path.setAttribute('d', 'M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6Zm13 0a3 3 0 1 0-6 0 3 3 0 1 0 6 0Z');
    icon.append(path);
    const label = document.createElement('span');
    label.textContent = 'Tampilkan kata sandi';
    toggle.append(icon, label);
    toggle.setAttribute('aria-pressed', 'false');
    toggle.style.cssText = 'gap:.35rem;color:#1e429f;font-size:.8rem;margin:.35rem 0 .5rem';

    toggle.addEventListener('click', () => {
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        label.textContent = show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi';
        toggle.setAttribute('aria-pressed', String(show));
    });

    input.after(toggle);
});
