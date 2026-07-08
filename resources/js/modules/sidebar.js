const STORAGE_KEY = 'siosmar-sidebar';

export function initSidebar() {
    const shell = document.querySelector('.app-shell');
    if (!shell) return;

    // restore saved state (desktop only)
    const collapsed = localStorage.getItem(STORAGE_KEY) === 'collapsed';
    if (collapsed && window.innerWidth > 1024) {
        shell.setAttribute('data-sidebar', 'collapsed');
    }

    // create mobile overlay backdrop once
    let backdrop = document.querySelector('.sidebar-backdrop');
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'sidebar-backdrop';
        document.body.appendChild(backdrop);
    }

    const closeMobile = () => {
        shell.setAttribute('data-sidebar', '');
    };

    backdrop.addEventListener('click', closeMobile);

    document.querySelectorAll('[data-sidebar-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const isCollapsed = shell.getAttribute('data-sidebar') === 'collapsed';
            const isOpenMobile = shell.getAttribute('data-sidebar') === 'open';

            if (window.innerWidth <= 1024) {
                shell.setAttribute('data-sidebar', isOpenMobile ? '' : 'open');
                return;
            }

            shell.setAttribute('data-sidebar', isCollapsed ? '' : 'collapsed');
            localStorage.setItem(STORAGE_KEY, isCollapsed ? 'expanded' : 'collapsed');
        });
    });

    // auto-close mobile overlay on resize to desktop
    window.addEventListener('resize', () => {
        if (window.innerWidth > 1024 && shell.getAttribute('data-sidebar') === 'open') {
            shell.setAttribute('data-sidebar', '');
        }
    });
}