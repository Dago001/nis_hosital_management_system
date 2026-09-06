// Bundle Lucide icons locally so the UI renders on restricted / offline networks
// (no dependency on unpkg.com at runtime). The existing views call
// `lucide.createIcons()` with no arguments, so we expose a compatible global.
import { createIcons, icons } from 'lucide';

window.lucide = {
    icons,
    createIcons(options = {}) {
        createIcons({ icons, ...options });
    },
};

// Render icons present on first paint and whenever the DOM is updated by page scripts.
document.addEventListener('DOMContentLoaded', () => window.lucide.createIcons());
