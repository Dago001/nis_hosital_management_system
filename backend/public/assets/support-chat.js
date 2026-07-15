// Nigeria Immigration Service Hospital - Real-time Support Chat Widget
(function () {
    // 1. Create HTML Elements and append to document body
    const widgetHTML = `
        <!-- Chat Bubble Trigger -->
        <button id="nis-chat-trigger" class="fixed bottom-6 right-6 z-[9999] bg-emerald-600 hover:bg-emerald-700 text-white p-3.5 rounded-full shadow-2xl transition duration-300 hover:scale-105 cursor-pointer flex items-center justify-center border border-emerald-500/10">
            <i data-lucide="message-square" id="nis-chat-icon" class="w-6 h-6"></i>
        </button>

        <!-- Chat Container Window (Mobile responsive) -->
        <div id="nis-chat-window" class="hidden fixed bottom-24 right-6 z-[9999] w-[calc(100vw-3rem)] sm:w-[360px] h-[480px] bg-white border border-slate-200 shadow-2xl rounded-3xl overflow-hidden flex flex-col font-sans">
            <!-- Header -->
            <div class="bg-[#006633] text-white px-4 py-3.5 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-2">
                    <img src="/assets/nis_logo-R4erN-9J.jpg" alt="Logo" class="h-6 w-6 object-contain rounded bg-white p-0.5">
                    <div>
                        <h4 class="text-xs font-extrabold tracking-tight">NIS Support Desk</h4>
                        <div class="flex items-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-450 animate-pulse"></span>
                            <span class="text-[8px] text-emerald-250 font-bold uppercase tracking-wider">Online & Responsive</span>
                        </div>
                    </div>
                </div>
                <button id="nis-chat-close" class="text-white/80 hover:text-white transition cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Body Content (Init / Chat Screens) -->
            <div id="nis-chat-body" class="flex-grow p-4 bg-slate-50 overflow-y-auto flex flex-col justify-between">
                <!-- Init Screen (Prompt visitor name) -->
                <div id="nis-chat-init-screen" class="flex-grow flex flex-col justify-center items-center text-center space-y-4 px-2">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-full">
                        <i data-lucide="heart-handshake" class="w-6 h-6 animate-bounce"></i>
                    </div>
                    <div>
                        <h5 class="text-xs font-bold text-slate-900">Start Support Chat</h5>
                        <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">
                            Welcome! Please enter your name to connect in real-time with our customer support center.
                        </p>
                    </div>
                    <form id="nis-chat-init-form" class="w-full space-y-3">
                        <input type="text" id="nis-visitor-name" required placeholder="Enter your full name" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-1 focus:ring-emerald-600 outline-none bg-white">
                        <button type="submit" class="w-full bg-[#006633] hover:bg-emerald-700 text-white py-2.5 rounded-xl text-xs font-bold transition shadow-sm">
                            Connect to Support
                        </button>
                    </form>
                </div>

                <!-- Active Chat Messages Screen -->
                <div id="nis-chat-active-screen" class="hidden flex-grow flex flex-col h-full justify-between">
                    <div id="nis-chat-messages-container" class="flex-grow space-y-3 overflow-y-auto pr-1 text-xs mb-3 flex flex-col">
                        <!-- Messages dynamically loaded here -->
                    </div>
                    <form id="nis-chat-send-form" class="flex items-center gap-2 border-t border-slate-100 pt-3 bg-white -mx-4 -mb-4 p-3 shrink-0">
                        <input type="text" id="nis-chat-input" required placeholder="Type support message..." 
                               class="flex-grow px-3 py-2 border border-slate-200 rounded-xl text-xs outline-none focus:ring-1 focus:ring-emerald-600">
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white p-2 rounded-xl transition cursor-pointer">
                            <i data-lucide="send" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    `;

    // Append to body
    const div = document.createElement('div');
    div.innerHTML = widgetHTML;
    document.body.appendChild(div);

    // Elements
    const trigger = document.getElementById('nis-chat-trigger');
    const chatWindow = document.getElementById('nis-chat-window');
    const closeBtn = document.getElementById('nis-chat-close');
    const initForm = document.getElementById('nis-chat-init-form');
    const sendForm = document.getElementById('nis-chat-send-form');
    const initScreen = document.getElementById('nis-chat-init-screen');
    const activeScreen = document.getElementById('nis-chat-active-screen');
    const messagesContainer = document.getElementById('nis-chat-messages-container');
    const chatInput = document.getElementById('nis-chat-input');
    const visitorNameInput = document.getElementById('nis-visitor-name');

    let pollInterval = null;
    let lastMessageCount = 0;

    // Toggle Chat Window
    trigger.addEventListener('click', () => {
        chatWindow.classList.toggle('hidden');
        if (!chatWindow.classList.contains('hidden')) {
            visitorNameInput.focus();
            checkSessionState();
        }
    });

    closeBtn.addEventListener('click', () => {
        chatWindow.classList.add('hidden');
    });

    // Check existing session
    function checkSessionState() {
        const token = localStorage.getItem('nis_chat_token');
        if (token) {
            initScreen.classList.add('hidden');
            activeScreen.classList.remove('hidden');
            loadMessages();
            // Start real-time polling every 3 seconds
            if (!pollInterval) {
                pollInterval = setInterval(loadMessages, 3000);
            }
        } else {
            initScreen.classList.remove('hidden');
            activeScreen.classList.add('hidden');
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        }
    }

    // Initialize session
    initForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const name = visitorNameInput.value.trim();
        if (!name) return;

        try {
            const res = await fetch('/api/chat/session/init', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ visitor_name: name })
            });
            const data = await res.json();
            if (res.ok) {
                localStorage.setItem('nis_chat_token', data.token);
                localStorage.setItem('nis_chat_visitor_name', name);
                checkSessionState();
            }
        } catch (err) {
            console.error('Failed to init support session:', err);
        }
    });

    // Fetch messages
    async function loadMessages() {
        const token = localStorage.getItem('nis_chat_token');
        if (!token) return;

        try {
            const res = await fetch(`/api/chat/messages?token=${token}`);
            const data = await res.json();
            if (res.ok) {
                // If support session is closed
                if (data.status === 'closed') {
                    localStorage.removeItem('nis_chat_token');
                    localStorage.removeItem('nis_chat_visitor_name');
                    checkSessionState();
                    return;
                }

                const msgs = data.messages;
                renderMessages(msgs);
            }
        } catch (err) {
            console.error('Failed to load support messages:', err);
        }
    }

    // Render Messages
    function renderMessages(msgs) {
        // Only re-render if message count changes
        if (msgs.length === lastMessageCount) return;
        lastMessageCount = msgs.length;

        messagesContainer.innerHTML = msgs.map(msg => {
            const isVisitor = msg.sender === 'visitor';
            const bgClass = isVisitor ? 'bg-emerald-600 text-white rounded-br-none ml-auto' : 'bg-white text-slate-800 rounded-bl-none border border-slate-150';
            return `
                <div class="max-w-[80%] p-2.5 rounded-2xl text-[11px] font-sans leading-relaxed shadow-sm ${bgClass}">
                    <p>${msg.message}</p>
                    <div class="text-[8px] text-right mt-1 opacity-70">
                        ${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </div>
                </div>
            `;
        }).join('');

        // Scroll to bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Send Message
    sendForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = chatInput.value.trim();
        const token = localStorage.getItem('nis_chat_token');
        if (!text || !token) return;

        chatInput.value = '';

        try {
            const res = await fetch('/api/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Visitor-Token': token
                },
                body: JSON.stringify({ message: text })
            });
            if (res.ok) {
                loadMessages();
            }
        } catch (err) {
            console.error('Failed to send support message:', err);
        }
    });

    // Setup initial icons
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
})();
