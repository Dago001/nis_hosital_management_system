<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'NIS Medical Services Portal')</title>
    <link rel="icon" type="image/jpeg" href="/images/nis_logo.jpg">
    <link rel="apple-touch-icon" href="/images/nis_logo.jpg">
    <!-- Google Fonts (progressive enhancement; falls back to system fonts offline) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Locally bundled Tailwind CSS + Lucide icons (works on restricted networks) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
</head>
<body class="h-full font-sans transition-colors duration-200">
    <!-- Auth Guard Check: Redirect to login immediately if no token -->
    <script>
        const token = localStorage.getItem('nis_hms_token');
        const userJson = localStorage.getItem('nis_hms_user');
        if (!token || !userJson) {
            window.location.href = '/login';
        }
        const user = JSON.parse(userJson || '{}');

        // Apply saved theme preference
        const savedTheme = localStorage.getItem('nis_hms_theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <div class="flex h-screen overflow-hidden">
        <!-- Mobile sidebar overlay -->
        <div id="sidebar-overlay" onclick="closeSidebar()" class="hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-30 lg:hidden"></div>

        <!-- Sidebar -->
        <aside id="app-sidebar" class="fixed lg:static inset-y-0 left-0 w-64 max-w-[80%] bg-emerald-800 text-white flex flex-col flex-shrink-0 border-r border-emerald-900/20 z-40 h-screen transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
            <!-- Brand logo (fixed at top) -->
            <div class="p-4 flex items-center gap-3 border-b border-emerald-900/20 shrink-0">
                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center p-1 shadow-sm">
                    <img src="/images/nis_logo.jpg" alt="NIS Crest" onerror="this.src='/favicon.svg'" class="w-8 h-8 object-contain" />
                </div>
                <div class="flex flex-col">
                    <span class="font-bold text-sm leading-tight tracking-wider">NIS HOSPITALS</span>
                    <span class="text-[10px] text-emerald-300 font-semibold tracking-wide">NIS Medical Services Portal</span>
                </div>
                <button onclick="closeSidebar()" class="ml-auto p-1.5 rounded-lg hover:bg-emerald-700/40 lg:hidden" aria-label="Close menu">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Navigation menu (scrollable) -->
            <nav class="flex-1 min-h-0 overflow-y-auto mt-4 px-3 pb-2 space-y-1" id="sidebar-nav"
                 style="scrollbar-width:thin;scrollbar-color:rgba(255,255,255,0.2) transparent;">
                <!-- Dynamic Links Injected by JS -->
            </nav>

            <!-- Sidebar footer / Sign out (fixed at bottom) -->
            <div class="p-4 border-t border-emerald-900/20 shrink-0">
                <button onclick="handleLogout()" class="w-full flex items-center gap-3 px-3 py-2 text-xs font-semibold text-emerald-100 hover:text-white hover:bg-emerald-700/40 rounded-xl transition-all">
                    <i data-lucide="log-out" class="w-4.5 h-4.5"></i>
                    <span>Sign Out Session</span>
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Header Navbar -->
            <header class="h-16 border-b border-slate-200 dark:border-slate-800/80 bg-white dark:bg-slate-900 flex items-center justify-between px-3 sm:px-6 z-10 shrink-0">
                <!-- Left: hamburger (mobile only) -->
                <div>
                    <button onclick="openSidebar()" class="p-2 -ml-1 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all lg:hidden" aria-label="Open menu">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Right Tools -->
                <div class="flex items-center gap-1 sm:gap-2">

                    <!-- Theme Toggle -->
                    <button onclick="toggleTheme()" class="p-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Toggle Theme">
                        <i id="theme-icon" data-lucide="sun" class="w-4.5 h-4.5"></i>
                    </button>

                    <!-- Bell Notifications -->
                    <div class="relative" id="notif-wrapper">
                        <button onclick="toggleNotifications()" id="notif-btn" class="relative p-2 text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition-all" title="Notifications">
                            <i data-lucide="bell" class="w-4.5 h-4.5"></i>
                            <span id="notif-badge" class="hidden absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[8px] font-black rounded-full flex items-center justify-center">0</span>
                        </button>

                        <!-- Notification Dropdown -->
                        <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-[calc(100vw-1.5rem)] max-w-xs sm:w-80 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl z-40 overflow-hidden">
                            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <span class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wide">Notifications</span>
                                <button onclick="clearNotifications()" class="text-[10px] text-emerald-600 font-bold hover:underline">Mark all read</button>
                            </div>
                            <div id="notif-list" class="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                                <div class="p-5 text-center text-slate-400 text-xs">
                                    <i data-lucide="bell-off" class="w-6 h-6 mx-auto mb-1.5 opacity-50"></i>
                                    No new notifications
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Avatar + Name -->
                    <div class="relative">
                        <button onclick="toggleProfileDropdown()" class="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition focus:outline-none" id="avatar-dropdown-trigger">
                            <div class="w-8 h-8 rounded-full bg-emerald-600 border-2 border-emerald-500/20 text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm shrink-0" id="avatar-container">
                                <span id="avatar-initials">U</span>
                            </div>
                            <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 hidden sm:block" id="header-user-name">User</span>
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400"></i>
                        </button>

                        <!-- Profile Dropdown -->
                        <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-52 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl py-2 z-30">
                            <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800 mb-1">
                                <p class="text-xs font-bold text-slate-800 dark:text-white" id="dropdown-user-name">User</p>
                                <p class="text-[10px] text-slate-500 dark:text-slate-400 capitalize" id="dropdown-user-role">Staff</p>
                            </div>
                            <button onclick="openProfileModal()" class="w-full text-left px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2.5 transition">
                                <i data-lucide="user" class="w-3.5 h-3.5"></i>
                                <span>My Profile Settings</span>
                            </button>
                            <button onclick="toggleMfa()" class="w-full text-left px-4 py-2.5 text-xs text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center gap-2.5 transition">
                                <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                                <span>2FA Security Toggle</span>
                            </button>
                            <hr class="border-slate-100 dark:border-slate-800 my-1">
                            <button onclick="handleLogout()" class="w-full text-left px-4 py-2.5 text-xs text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 flex items-center gap-2.5 transition font-semibold">
                                <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                                <span>Sign Out</span>
                            </button>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Page Body Content -->
            <main class="flex-1 overflow-y-auto bg-slate-50 dark:bg-slate-950/40 flex flex-col">
                <div class="flex-grow p-4 sm:p-6">
                    @yield('content')
                </div>

                <!-- Internal Dashboard Footer -->
                <footer class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-6 py-4 mt-auto shrink-0">
                    <div class="flex items-center justify-center gap-1.5 text-[9px] text-slate-400 dark:text-slate-500 font-semibold">
                        <i data-lucide="shield-check" class="w-3 h-3 text-emerald-500 shrink-0"></i>
                        <span>&copy; <span id="footer-year"></span> All Right Reserved | Nigeria Immigration Service</span>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <!-- My Profile Modal -->
    <div id="profile-modal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 w-full max-w-md shadow-2xl relative">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">My Account Profile</h3>
            
            <form id="profile-form" onsubmit="handleUpdateProfile(event)" class="space-y-4">
                <!-- Avatar Upload Section -->
                <div class="flex flex-col items-center gap-3 border-b border-slate-100 dark:border-slate-800 pb-4 mb-4">
                    <div class="w-20 h-20 rounded-full bg-emerald-600 border-2 border-emerald-500/20 text-white flex items-center justify-center font-bold text-2xl uppercase shadow-md relative overflow-hidden" id="modal-avatar-preview">
                        <span id="modal-avatar-initials">U</span>
                    </div>
                    <label class="cursor-pointer bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-[10px] font-semibold text-slate-800 dark:text-slate-200 px-3 py-1.5 rounded-full border border-slate-200 dark:border-slate-700 transition">
                        Select Photo
                        <input type="file" id="avatar-input" class="hidden" accept="image/*" onchange="handleAvatarUpload(event)">
                    </label>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Full Name</label>
                    <input type="text" id="profile-name" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Phone Number</label>
                    <input type="text" id="profile-phone" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeProfileModal()" class="px-4 py-2 text-xs font-semibold text-slate-800 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl transition">Cancel</button>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-xs font-semibold rounded-xl transition shadow-md">Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    <!-- API Client Helper, Theme and Page Initialization -->
    <script>
        // Custom dynamic API request helper
        window.api = {
            baseUrl: (() => {
                if (window.location.port === '5173' || window.location.port === '5174') {
                    return 'http://localhost:8000/api';
                }
                const publicIdx = window.location.pathname.indexOf('/public');
                if (publicIdx !== -1) {
                    return window.location.pathname.substring(0, publicIdx + 7) + '/api';
                }
                return '/api';
            })(),

            async request(endpoint, options = {}) {
                const url = `${this.baseUrl}${endpoint}`;
                options.headers = options.headers || {};
                
                const token = localStorage.getItem('nis_hms_token');
                if (token) {
                    options.headers['Authorization'] = `Bearer ${token}`;
                }
                options.headers['Accept'] = 'application/json';
                if (!(options.body instanceof FormData)) {
                    options.headers['Content-Type'] = 'application/json';
                }

                try {
                    const response = await fetch(url, options);
                    if (response.status === 401) {
                        // Unauthorized -> logout
                        localStorage.removeItem('nis_hms_token');
                        localStorage.removeItem('nis_hms_user');
                        window.location.href = '/login';
                        throw new Error('Unauthorized');
                    }
                    const data = await response.json();
                    if (!response.ok) {
                        throw new Error(data.message || 'Request failed');
                    }
                    return data;
                } catch (error) {
                    console.error('API Error:', error);
                    throw error;
                }
            },

            async get(endpoint) {
                return this.request(endpoint, { method: 'GET' });
            },

            async post(endpoint, body) {
                return this.request(endpoint, {
                    method: 'POST',
                    body: body instanceof FormData ? body : JSON.stringify(body)
                });
            },

            async put(endpoint, body) {
                return this.request(endpoint, {
                    method: 'PUT',
                    body: body instanceof FormData ? body : JSON.stringify(body)
                });
            },

            async delete(endpoint) {
                return this.request(endpoint, { method: 'DELETE' });
            }
        };

        // ─── Responsive Sidebar (mobile off-canvas drawer) ───────────────────
        function openSidebar() {
            document.getElementById('app-sidebar')?.classList.remove('-translate-x-full');
            document.getElementById('sidebar-overlay')?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            document.getElementById('app-sidebar')?.classList.add('-translate-x-full');
            document.getElementById('sidebar-overlay')?.classList.add('hidden');
            document.body.style.overflow = '';
        }
        // Reset drawer state when crossing the desktop breakpoint.
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) closeSidebar();
        });

        // Theme controls
        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark');
            const icon = document.getElementById('theme-icon');
            if (icon) {
                icon.setAttribute('data-lucide', isDark ? 'sun' : 'moon');
                lucide.createIcons();
            }
        }

        function toggleTheme() {
            const html = document.documentElement;
            if (html.classList.contains('dark')) {
                html.classList.remove('dark');
                localStorage.setItem('nis_hms_theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('nis_hms_theme', 'dark');
            }
            updateThemeIcon();
        }

        // Profile Modal controls
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profile-dropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', (e) => {
            const trigger = document.getElementById('avatar-dropdown-trigger');
            const dropdown = document.getElementById('profile-dropdown');
            if (trigger && !trigger.contains(e.target) && dropdown && !dropdown.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        function openProfileModal() {
            document.getElementById('profile-name').value = user.name || '';
            document.getElementById('profile-phone').value = user.staff?.phone || '';
            document.getElementById('profile-modal').classList.remove('hidden');
            document.getElementById('profile-dropdown').classList.add('hidden');
        }

        function closeProfileModal() {
            document.getElementById('profile-modal').classList.add('hidden');
        }

        async function handleUpdateProfile(e) {
            e.preventDefault();
            const name = document.getElementById('profile-name').value;
            const phone = document.getElementById('profile-phone').value;
            try {
                const res = await api.put('/profile', { name, phone });
                user.name = res.user.name;
                user.staff = res.user.staff;
                localStorage.setItem('nis_hms_user', JSON.stringify(user));
                updateUIForUser();
                closeProfileModal();
                alert('Profile updated successfully!');
            } catch (err) {
                alert(err.message || 'Failed to update profile');
            }
        }

        async function handleAvatarUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            const formData = new FormData();
            formData.append('avatar', file);

            try {
                const res = await api.post('/profile/avatar', formData);
                user.avatar = res.user.avatar;
                localStorage.setItem('nis_hms_user', JSON.stringify(user));
                updateUIForUser();
                alert('Avatar uploaded successfully!');
            } catch (err) {
                alert(err.message || 'Failed to upload photo');
            }
        }

        async function toggleMfa() {
            try {
                const res = await api.post('/toggle-mfa');
                user.mfa_enabled = res.mfa_enabled;
                localStorage.setItem('nis_hms_user', JSON.stringify(user));
                alert(`Multi-Factor Authentication (2FA) is now ${res.mfa_enabled ? 'ENABLED' : 'DISABLED'}.`);
            } catch (err) {
                alert(err.message || 'Failed to toggle 2FA');
            }
        }

        function handleLogout() {
            api.post('/logout', {}).catch(() => {}).finally(() => {
                localStorage.removeItem('nis_hms_token');
                localStorage.removeItem('nis_hms_user');
                localStorage.removeItem('nis_hms_theme');
                window.location.href = '/login';
            });
        }

        // ─── Notification System ──────────────────────────────────────────────
        let notifData = [];
        let notifPoll = null;

        function toggleNotifications() {
            const dd = document.getElementById('notif-dropdown');
            dd.classList.toggle('hidden');
            document.getElementById('profile-dropdown').classList.add('hidden');
        }

        async function clearNotifications() {
            try {
                await api.post('/notifications/read-all', {});
                notifData = notifData.map(n => ({ ...n, is_read: true }));
                renderNotifications();
                fetchNotifications();
            } catch (e) {
                // ignore
            }
        }

        async function markNotificationRead(id) {
            const n = notifData.find(x => x.id === id);
            if (!n || n.is_read) return;
            n.is_read = true;
            renderNotifications();
            try { await api.post(`/notifications/${encodeURIComponent(id)}/read`, {}); } catch (e) {}
        }

        async function fetchNotifications() {
            try {
                const res = await api.get('/notifications');
                const items = res.notifications || [];
                // Re-render whenever the set or read-state changes.
                if (JSON.stringify(items) !== JSON.stringify(notifData)) {
                    notifData = items;
                    renderNotifications();
                }
            } catch (e) {
                // Silently fail — notifications are non-critical
            }
        }

        function renderNotifications() {
            const list = document.getElementById('notif-list');
            const badge = document.getElementById('notif-badge');
            if (!list || !badge) return;

            const unread = notifData.filter(n => !n.is_read).length;
            if (unread > 0) {
                badge.textContent = unread > 9 ? '9+' : unread;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            if (notifData.length === 0) {
                list.innerHTML = `
                    <div class="p-6 text-center text-slate-400 text-xs">
                        <i data-lucide="bell-off" class="w-6 h-6 mx-auto mb-1.5 opacity-40"></i>
                        No new notifications
                    </div>`;
                lucide.createIcons();
                return;
            }

            const iconMap = {
                appointment: { icon: 'calendar', color: 'text-blue-600 bg-blue-50 dark:bg-blue-500/10' },
                message:     { icon: 'message-circle', color: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10' },
                patient:     { icon: 'user-check', color: 'text-indigo-600 bg-indigo-50 dark:bg-indigo-500/10' },
                lab:         { icon: 'test-tube', color: 'text-amber-600 bg-amber-50 dark:bg-amber-500/10' },
                pharmacy:    { icon: 'pill', color: 'text-purple-600 bg-purple-50 dark:bg-purple-500/10' },
                general:     { icon: 'bell', color: 'text-slate-500 bg-slate-100 dark:bg-slate-800' }
            };

            list.innerHTML = notifData.slice(0, 20).map(n => {
                const cfg = iconMap[n.type] || iconMap.general;
                const timeAgo = n.created_at ? formatTimeAgo(n.created_at) : '';
                return `
                    <div onclick="markNotificationRead('${(n.id + '').replace(/'/g, "")}')" class="flex gap-3 items-start px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition ${n.is_read ? 'opacity-60' : ''} cursor-pointer">
                        <div class="shrink-0 p-2 rounded-xl ${cfg.color}">
                            <i data-lucide="${cfg.icon}" class="w-3.5 h-3.5"></i>
                        </div>
                        <div class="flex-grow min-w-0">
                            <p class="text-[11px] font-bold text-slate-800 dark:text-white leading-snug">${n.title || 'Notification'}</p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 leading-snug truncate">${n.message || ''}</p>
                            <span class="text-[9px] text-slate-400 font-semibold mt-1 block">${timeAgo}</span>
                        </div>
                        ${!n.is_read ? '<span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 mt-1"></span>' : ''}
                    </div>`;
            }).join('');
            lucide.createIcons();
        }

        function formatTimeAgo(dateStr) {
            const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
            if (diff < 60) return `${diff}s ago`;
            if (diff < 3600) return `${Math.floor(diff/60)}m ago`;
            if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
            return `${Math.floor(diff/86400)}d ago`;
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            const notifWrapper = document.getElementById('notif-wrapper');
            const notifDd = document.getElementById('notif-dropdown');
            if (notifWrapper && !notifWrapper.contains(e.target) && notifDd) {
                notifDd.classList.add('hidden');
            }
        });

        // Initialize User Details on UI
        function updateUIForUser() {
            if (!user.name) return;

            // Header name
            const nameEl = document.getElementById('header-user-name');
            if (nameEl) nameEl.innerText = user.name;

            // Dropdown name + role
            const ddName = document.getElementById('dropdown-user-name');
            const ddRole = document.getElementById('dropdown-user-role');
            const roleName = user.roles && user.roles[0] ? user.roles[0].name.replace(/_/g, ' ') : 'Staff';
            if (ddName) ddName.innerText = user.name;
            if (ddRole) ddRole.innerText = roleName;

            // Setup avatars
            const initials = user.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            const avatarInitEl = document.getElementById('avatar-initials');
            const modalInitEl = document.getElementById('modal-avatar-initials');
            if (avatarInitEl) avatarInitEl.innerText = initials;
            if (modalInitEl) modalInitEl.innerText = initials;

            const avatarContainer = document.getElementById('avatar-container');
            const modalPreview = document.getElementById('modal-avatar-preview');

            if (user.avatar) {
                const avatarUrl = user.avatar.startsWith('http') ? user.avatar : `/storage/${user.avatar}`;
                if (avatarContainer) avatarContainer.innerHTML = `<img src="${avatarUrl}" class="w-full h-full object-cover rounded-full" />`;
                if (modalPreview) modalPreview.innerHTML = `<img src="${avatarUrl}" class="w-full h-full object-cover rounded-full" />`;
            } else {
                if (avatarContainer) avatarContainer.innerHTML = `<span id="avatar-initials">${initials}</span>`;
                if (modalPreview) modalPreview.innerHTML = `<span id="modal-avatar-initials">${initials}</span>`;
            }
        }

        // Initialize dynamic Sidebar Menu based on logged-in user role
        function renderSidebar() {
            const role = user.roles && user.roles[0] ? user.roles[0].name : '';
            const path = window.location.pathname;

            const menuItems = [
                { path: '/dashboard',    label: 'Dashboard',       icon: 'layout-dashboard',  roles: ['super_admin', 'hospital_admin', 'medical_director', 'chief_medical_officer', 'doctor', 'consultant', 'nurse', 'pharmacist', 'lab_scientist', 'radiographer', 'records_officer', 'cashier', 'account_officer', 'receptionist', 'health_info_officer', 'store_officer', 'inventory_officer', 'procurement_officer', 'hr_officer', 'ict_admin', 'ambulance_officer', 'ward_manager', 'theatre_manager', 'dental_officer', 'eye_clinic_officer', 'physiotherapist', 'staff', 'patient'] },
                { path: '/patients',     label: 'Patients',         icon: 'users',             roles: ['super_admin', 'hospital_admin', 'medical_director', 'chief_medical_officer', 'doctor', 'consultant', 'nurse', 'records_officer', 'cashier', 'receptionist', 'health_info_officer', 'ambulance_officer', 'ward_manager', 'theatre_manager', 'dental_officer', 'eye_clinic_officer', 'physiotherapist', 'staff', 'patient'] },
                { path: '/appointments', label: 'Appointments',     icon: 'calendar',          roles: ['super_admin', 'hospital_admin', 'chief_medical_officer', 'doctor', 'consultant', 'nurse', 'records_officer', 'receptionist', 'dental_officer', 'eye_clinic_officer'] },
                { path: '/queue',        label: 'Queue Management', icon: 'list-ordered',      roles: ['super_admin', 'hospital_admin', 'medical_director', 'doctor', 'consultant', 'nurse', 'receptionist', 'ward_manager'] },
                { path: '/emergencies',  label: 'Emergency (ER)',   icon: 'alert-octagon',     roles: ['super_admin', 'hospital_admin', 'doctor', 'nurse', 'ambulance_officer', 'ward_manager'] },
                { path: '/vitals',       label: 'Vitals Entry',     icon: 'activity',          roles: ['super_admin', 'nurse', 'ward_manager', 'ambulance_officer'] },
                { path: '/consultations',label: 'Consultation',     icon: 'stethoscope',       roles: ['super_admin', 'doctor', 'consultant', 'dental_officer', 'eye_clinic_officer', 'physiotherapist', 'theatre_manager'] },
                { path: '/ipd',          label: 'IPD / Ward Beds',  icon: 'building-2',        roles: ['super_admin', 'hospital_admin', 'medical_director', 'doctor', 'nurse', 'ward_manager'] },
                { path: '/referrals',    label: 'Referrals',        icon: 'arrow-right-left',  roles: ['super_admin', 'hospital_admin', 'medical_director', 'doctor', 'consultant', 'nurse'] },
                { path: '/laboratory',   label: 'Laboratory',       icon: 'test-tube',         roles: ['super_admin', 'medical_director', 'lab_scientist', 'radiographer', 'doctor'] },
                { path: '/pharmacy',     label: 'Pharmacy',         icon: 'pill',              roles: ['super_admin', 'medical_director', 'pharmacist', 'store_officer', 'inventory_officer', 'procurement_officer'] },
                { path: '/billing',      label: 'Billing & Cashier',icon: 'credit-card',       roles: ['super_admin', 'hospital_admin', 'medical_director', 'cashier', 'account_officer'] },
                { path: '/reports',      label: 'Analytics',        icon: 'bar-chart',         roles: ['super_admin', 'hospital_admin', 'medical_director', 'chief_medical_officer', 'health_info_officer', 'records_officer'] },
                { path: '/audit-trail',  label: 'Audit Trail',      icon: 'clipboard-list',    roles: ['super_admin', 'ict_admin', 'health_info_officer'] },
                { path: '/support-chats',label: 'Support Chats',    icon: 'message-circle',    roles: ['super_admin', 'hospital_admin', 'ict_admin', 'receptionist', 'records_officer', 'staff'] },
                { path: '/admin/users',  label: 'User Accounts',    icon: 'user-cog',          roles: ['super_admin', 'ict_admin', 'hr_officer', 'hospital_admin'] },
                { path: '/settings',     label: 'System Settings',  icon: 'settings',          roles: ['super_admin', 'ict_admin', 'hospital_admin'] },
            ];

            const filteredItems = menuItems.filter(item => item.roles.includes(role));
            const nav = document.getElementById('sidebar-nav');
            if (nav) {
                nav.innerHTML = filteredItems.map(item => {
                    const isActive = path === item.path || (item.path.length > 1 && path.startsWith(item.path));
                    const activeClass = isActive 
                        ? 'bg-emerald-900/60 text-white font-semibold' 
                        : 'text-emerald-100 hover:text-white hover:bg-emerald-700/30';
                    return `
                        <a href="${item.path}" class="flex items-center gap-3 px-3 py-2 text-xs rounded-xl transition-all ${activeClass}">
                            <i data-lucide="${item.icon}" class="w-5 h-5 shrink-0"></i>
                            <span>${item.label}</span>
                        </a>
                    `;
                }).join('');
                lucide.createIcons();
            }
        }

        // Trigger setups on load
        document.addEventListener('DOMContentLoaded', () => {
            updateUIForUser();
            renderSidebar();
            updateThemeIcon();
            lucide.createIcons();
            // Set footer year
            const fy = document.getElementById('footer-year');
            if (fy) fy.textContent = new Date().getFullYear();
            // Start real-time notification polling (every 30s)
            fetchNotifications();
            notifPoll = setInterval(fetchNotifications, 30000);
        });
    </script>
    @yield('scripts')
</body>
</html>
