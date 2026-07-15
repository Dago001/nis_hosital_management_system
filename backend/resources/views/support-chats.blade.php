@extends('layouts.app')

@section('title', 'Customer Support Command - NIS Medical Services Portal')

@section('content')
<div class="space-y-6 h-[calc(100vh-10rem)] flex flex-col">
    <!-- Header -->
    <div class="shrink-0">
        <h1 class="text-xl font-bold text-slate-800 dark:text-white font-sans font-black">Support Desk Command Center</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Manage real-time support channels and reply to active portal visitors.</p>
    </div>

    <!-- Main Workspace Split Pane -->
    <div class="flex-grow flex gap-6 overflow-hidden min-h-0">
        
        <!-- Left Pane: Active Sessions List -->
        <div class="w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden flex flex-col shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 shrink-0">
                <span class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="message-square" class="text-emerald-600"></i> Active Conversations
                </span>
            </div>
            
            <!-- Sessions Scroll Area -->
            <div id="sessions-list" class="flex-grow overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                <!-- Loaded Dynamically -->
                <div class="p-6 text-center text-slate-400">Loading support queues...</div>
            </div>
        </div>

        <!-- Right Pane: Active Conversation Chat Pane -->
        <div class="flex-grow bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden flex flex-col shadow-sm">
            <!-- Empty State -->
            <div id="chat-empty-state" class="flex-grow flex flex-col items-center justify-center text-center p-8 space-y-3">
                <div class="p-4 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 rounded-full">
                    <i data-lucide="messages-square" class="w-8 h-8"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Awaiting Channel Selection</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mt-1 leading-relaxed font-sans">
                        Select an active visitor conversation from the list to start responding in real-time.
                    </p>
                </div>
            </div>

            <!-- Active Chat Workspace -->
            <div id="chat-workspace" class="hidden flex-grow flex flex-col h-full overflow-hidden">
                <!-- Active Chat Header -->
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-emerald-650 text-white font-bold flex items-center justify-center uppercase" id="active-chat-avatar">
                            V
                        </div>
                        <div>
                            <h4 id="active-chat-name" class="text-xs font-bold text-slate-900 dark:text-white">Visitor</h4>
                            <div class="flex items-center gap-1 mt-0.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-505 animate-pulse"></span>
                                <span class="text-[8px] text-slate-400 font-bold uppercase tracking-wider">Channel Active</span>
                            </div>
                        </div>
                    </div>
                    <button onclick="closeActiveChatSession()" class="bg-red-50 hover:bg-red-100 dark:bg-red-500/10 text-red-600 hover:text-red-700 px-3 py-1.5 rounded-xl text-[10px] font-bold transition flex items-center gap-1 cursor-pointer">
                        <i data-lucide="power" class="w-3.5 h-3.5"></i> Close Chat Session
                    </button>
                </div>

                <!-- Messages Window -->
                <div id="chat-messages-container" class="flex-grow p-4 overflow-y-auto space-y-3 bg-slate-50/30 dark:bg-slate-950/20 text-xs">
                    <!-- Messages Dynamically Appended -->
                </div>

                <!-- Chat Input Send Form -->
                <form id="chat-send-form" onsubmit="handleSendReply(event)" class="p-4 border-t border-slate-100 dark:border-slate-800 flex items-center gap-3 bg-white dark:bg-slate-900 shrink-0">
                    <input type="text" id="chat-reply-input" required placeholder="Type support reply message..." 
                           class="flex-grow px-4 py-3 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs outline-none focus:ring-1 focus:ring-emerald-500 focus:bg-white dark:focus:bg-slate-950 text-slate-805 dark:text-slate-200 transition">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md cursor-pointer">
                        Send <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    let activeSessionId = null;
    let sessionsList = [];
    let activeMessages = [];
    let lastMsgCount = 0;
    
    let listPoll = null;
    let msgPoll = null;

    // Load active sessions
    async function loadSessions() {
        try {
            const res = await api.get('/admin/chats');
            sessionsList = res.sessions;
            renderSessionsList();
        } catch (err) {
            console.error('Failed to load support sessions:', err);
        }
    }

    // Render active sessions
    function renderSessionsList() {
        const container = document.getElementById('sessions-list');
        if (sessionsList.length === 0) {
            container.innerHTML = `<div class="p-8 text-center text-slate-400">No active support conversations.</div>`;
            return;
        }

        container.innerHTML = sessionsList.map(session => {
            const isActive = session.id === activeSessionId;
            const bgClass = isActive 
                ? 'bg-emerald-50 dark:bg-emerald-500/10 border-l-4 border-emerald-600' 
                : 'hover:bg-slate-50 dark:hover:bg-slate-800/20';

            const unreadBadge = session.messages_count > 0 
                ? `<span class="bg-red-550 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold ml-auto">${session.messages_count}</span>` 
                : '';

            return `
                <div onclick="selectSession(${session.id})" class="p-4 cursor-pointer transition flex items-center gap-3 ${bgClass}">
                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold flex items-center justify-center uppercase">
                        ${session.visitor_name[0]}
                    </div>
                    <div class="flex-grow min-w-0">
                        <div class="font-bold text-slate-900 dark:text-white truncate">${session.visitor_name}</div>
                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Active Support Request</div>
                    </div>
                    ${unreadBadge}
                </div>
            `;
        }).join('');
    }

    // Select support session
    async function selectSession(id) {
        activeSessionId = id;
        lastMsgCount = 0;
        
        // Show workspace
        document.getElementById('chat-empty-state').classList.add('hidden');
        document.getElementById('chat-workspace').classList.remove('hidden');

        const session = sessionsList.find(s => s.id === id);
        if (session) {
            document.getElementById('active-chat-name').innerText = session.visitor_name;
            document.getElementById('active-chat-avatar').innerText = session.visitor_name[0];
        }

        // Render sessions list immediately to clear unread badges
        renderSessionsList();

        // Fetch messages
        await loadMessages();

        // Setup message poll
        if (msgPoll) clearInterval(msgPoll);
        msgPoll = setInterval(loadMessages, 3000);
    }

    // Fetch messages for active session
    async function loadMessages() {
        if (!activeSessionId) return;

        try {
            const res = await api.get(`/admin/chats/${activeSessionId}`);
            activeMessages = res.messages;
            renderMessages();
        } catch (err) {
            console.error('Failed to load chat messages:', err);
        }
    }

    // Render messages inside conversation panel
    function renderMessages() {
        const container = document.getElementById('chat-messages-container');
        
        if (activeMessages.length === lastMsgCount) return;
        lastMsgCount = activeMessages.length;

        container.innerHTML = activeMessages.map(msg => {
            const isStaff = msg.sender === 'staff';
            const bgClass = isStaff 
                ? 'bg-emerald-600 text-white rounded-br-none ml-auto' 
                : 'bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-205 border border-slate-200 dark:border-slate-700 rounded-bl-none';

            const senderLabel = isStaff ? 'You (Support)' : 'Visitor';

            return `
                <div class="max-w-[75%] p-3 rounded-2xl shadow-sm space-y-1 ${bgClass}">
                    <div class="text-[8px] opacity-75 font-bold uppercase tracking-wider">${senderLabel}</div>
                    <p class="leading-relaxed">${msg.message}</p>
                    <div class="text-[8px] text-right opacity-70">
                        ${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </div>
                </div>
            `;
        }).join('');

        container.scrollTop = container.scrollHeight;
    }

    // Send Support Reply
    async function handleSendReply(e) {
        e.preventDefault();
        const input = document.getElementById('chat-reply-input');
        const text = input.value.trim();
        if (!text || !activeSessionId) return;

        input.value = '';

        try {
            await api.post(`/admin/chats/${activeSessionId}/reply`, { message: text });
            loadMessages();
        } catch (err) {
            alert(err.message || 'Failed to send reply.');
        }
    }

    // Close session
    async function closeActiveChatSession() {
        if (!activeSessionId) return;
        if (!confirm('Are you sure you want to close this customer support conversation?')) return;

        try {
            await api.post(`/admin/chats/${activeSessionId}/close`);
            alert('Support conversation closed successfully.');
            
            // Reset
            activeSessionId = null;
            document.getElementById('chat-empty-state').classList.remove('hidden');
            document.getElementById('chat-workspace').classList.add('hidden');
            
            if (msgPoll) {
                clearInterval(msgPoll);
                msgPoll = null;
            }

            loadSessions();
        } catch (err) {
            alert(err.message || 'Failed to close conversation.');
        }
    }

    // Init page controls
    document.addEventListener('DOMContentLoaded', () => {
        loadSessions();
        listPoll = setInterval(loadSessions, 4000);
    });

    // Cleanup interval on page leave
    window.addEventListener('beforeunload', () => {
        if (listPoll) clearInterval(listPoll);
        if (msgPoll) clearInterval(msgPoll);
    });
</script>
@endsection
