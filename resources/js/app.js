

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    document.querySelectorAll('[x-cloak]').forEach((el) => el.removeAttribute('x-cloak'));
});

Alpine.start();
