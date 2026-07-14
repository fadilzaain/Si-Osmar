export function initProfileMenu() {
    const trigger = document.querySelector('[data-action="toggle-profile-menu"]');
    const wrapper = trigger?.closest('.navbar-profile-wrapper, .sidebar-profile-wrapper');

    if (!trigger || !wrapper) return;

    trigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = wrapper.getAttribute('data-open') === 'true';
        wrapper.setAttribute('data-open', isOpen ? 'false' : 'true');
        trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    });

    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            wrapper.setAttribute('data-open', 'false');
            trigger.setAttribute('aria-expanded', 'false');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            wrapper.setAttribute('data-open', 'false');
            trigger.setAttribute('aria-expanded', 'false');
        }
    });
}