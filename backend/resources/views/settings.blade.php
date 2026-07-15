@extends('layouts.app')

@section('title', 'System Settings - NIS Medical Services Portal')

@section('content')
<div class="space-y-6 max-w-3xl mx-auto pb-10">
    <!-- Title -->
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i data-lucide="settings" class="text-emerald-600"></i> Hospital Administration & Settings
        </h1>
        <p class="text-xs text-slate-800 dark:text-slate-200 font-sans">Configure clinic metadata variables, branches, parameters, and branding assets</p>
    </div>

    <!-- Main System Form -->
    <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <form id="settings-form" onsubmit="handleSaveSettings(event)" class="space-y-6">
            <div class="space-y-4">
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">Demographic branding</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200">Hospital Facility Name</label>
                        <input type="text" id="facility_name" required class="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200">Active Branch Division</label>
                        <input type="text" id="active_branch" required class="w-full mt-1.5 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- System Controls -->
            <div class="space-y-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider">System Operations</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-800 dark:text-slate-200 block mb-1">Maintenance Mode</label>
                        <select id="maintenance_mode" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <option value="0">Inactive (Public Portal Online)</option>
                            <option value="1">Active (Public Portal Offline / Under Maintenance)</option>
                        </select>
                        <p class="text-[9px] text-slate-500 dark:text-slate-400 mt-1 font-semibold leading-relaxed flex items-center gap-1">
                            <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-amber-500 shrink-0"></i>
                            When active, members of the public visiting the landing page will see a Maintenance notice page. Staff access and internal dashboards will remain fully operational.
                        </p>
                    </div>
                </div>
            </div>

            <!-- RESTFUL API HOOKS & INTEGRATIONS -->
            <div class="space-y-6 border-t border-slate-100 dark:border-slate-800 pt-6">
                <div>
                    <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="webhook" class="w-4 h-4 text-emerald-600"></i> RESTful API Hooks & Integrations
                    </h3>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-sans mt-0.5">Manage real-time data sync channels with state departments and external health systems</p>
                </div>

                <!-- 1. Incoming API (Other systems pull from this app) -->
                <div class="p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 space-y-4">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Incoming API Integration (Data Pull)
                        </h4>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-sans mt-0.5">Allows external medical authorities to query patients records and triage data in real-time.</p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-700 dark:text-slate-350 block mb-1">External API Access Key</label>
                            <div class="flex gap-2">
                                <input type="text" id="external_api_token" placeholder="Generate secure token for authentication" class="flex-grow px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl text-xs text-slate-850 dark:text-slate-100 font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                <button type="button" onclick="generateApiKey()" class="bg-slate-800 hover:bg-slate-900 text-white text-xs px-4 py-2 rounded-xl font-bold transition shrink-0 cursor-pointer">
                                    Generate Key
                                </button>
                            </div>
                        </div>

                        <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 space-y-1.5">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Integration URL endpoint</span>
                            <code class="text-[10px] text-emerald-600 dark:text-emerald-400 font-mono break-all select-all block" id="incoming_endpoint_preview">
                                GET /api/external/sync-patients?api_key=[TOKEN]
                            </code>
                        </div>
                    </div>
                </div>

                <!-- 2. Outgoing API (This app pulls from / pushes to other systems) -->
                <div class="p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200/80 dark:border-slate-800/80 space-y-5">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-white flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Outgoing API Hooks (Data Fetch & Push)
                        </h4>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 font-sans mt-0.5">Configure endpoints to push events (Webhooks) or fetch global registries from external state portals.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <!-- Fetch -->
                        <div>
                            <label class="text-[10px] font-bold text-slate-700 dark:text-slate-350 block mb-1">External Data Registry Source URL (GET)</label>
                            <input type="url" id="integration_fetch_url" placeholder="e.g. https://api.health.gov.ng/drugs/registry" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <p class="text-[9px] text-slate-500 mt-1 font-semibold leading-relaxed">
                                URL endpoint to fetch real-time drug guidelines or security clearance datasets from external directories.
                            </p>
                        </div>

                        <!-- Webhook URL -->
                        <div class="border-t border-slate-200/50 dark:border-slate-800/50 pt-4">
                            <label class="text-[10px] font-bold text-slate-700 dark:text-slate-350 block mb-1">Webhook Delivery Destination URL (POST)</label>
                            <input type="url" id="webhook_url" placeholder="e.g. https://external-system.com/webhooks/nis-sync" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl text-xs text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Webhook Secret -->
                            <div>
                                <label class="text-[10px] font-bold text-slate-700 dark:text-slate-350 block mb-1">Webhook HMAC Signature Secret</label>
                                <input type="text" id="webhook_secret" placeholder="Secret key to sign payload signatures" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl text-xs text-slate-800 dark:text-slate-100 font-mono focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </div>

                            <!-- Webhook Events -->
                            <div>
                                <label class="text-[10px] font-bold text-slate-700 dark:text-slate-350 block mb-1">Trigger Webhook Events</label>
                                <select id="webhook_events" class="w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 rounded-xl text-xs text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    <option value="patient.registered">Patient Registered (patient.registered)</option>
                                    <option value="triage.completed">Triage Vitals Captured (triage.completed)</option>
                                    <option value="consultation.submitted">SOAP Encounter Filed (consultation.submitted)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Integration Live Testing Section -->
                    <div class="border-t border-slate-200/50 dark:border-slate-800/50 pt-4 space-y-3">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Connection Testing Console</span>
                        <div class="flex flex-wrap gap-2.5">
                            <button type="button" onclick="testOutgoingFetch()" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-4 py-2.5 rounded-xl font-bold transition flex items-center gap-1.5 cursor-pointer shadow-md shadow-blue-500/10">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Test Source Pull
                            </button>
                            <button type="button" onclick="testWebhookDispatch()" class="bg-amber-600 hover:bg-amber-700 text-white text-xs px-4 py-2.5 rounded-xl font-bold transition flex items-center gap-1.5 cursor-pointer shadow-md shadow-amber-500/10">
                                <i data-lucide="send" class="w-3.5 h-3.5"></i> Test Dispatch Webhook
                            </button>
                        </div>

                        <!-- Console Output Block -->
                        <div id="test_console_wrapper" class="hidden p-3 bg-slate-900 dark:bg-black rounded-xl border border-slate-800 space-y-2">
                            <div class="flex items-center justify-between text-[9px] font-bold text-slate-500">
                                <span>INTEGRATION TEST RESULTS OUTPUT</span>
                                <button type="button" onclick="clearConsole()" class="text-red-400 hover:text-red-300">Clear</button>
                            </div>
                            <pre class="text-[10px] text-emerald-400 font-mono overflow-x-auto whitespace-pre-wrap leading-relaxed max-h-48" id="test_console">{}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-slate-800">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-semibold shadow-lg shadow-emerald-650/10 transition cursor-pointer">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function loadSettings() {
        try {
            const res = await api.get('/settings');
            const settings = res.settings;

            document.getElementById('facility_name').value = settings.facility_name;
            document.getElementById('active_branch').value = settings.active_branch;
            document.getElementById('maintenance_mode').value = settings.maintenance_mode;
            document.getElementById('external_api_token').value = settings.external_api_token || '';
            document.getElementById('webhook_url').value = settings.webhook_url || '';
            document.getElementById('webhook_secret').value = settings.webhook_secret || '';
            document.getElementById('webhook_events').value = settings.webhook_events || 'patient.registered';
            document.getElementById('integration_fetch_url').value = settings.integration_fetch_url || '';

            updateEndpointPreview(settings.external_api_token || '[TOKEN]');
        } catch (err) {
            alert('Failed to load system settings: ' + (err.message || 'connection error'));
        }
    }

    async function handleSaveSettings(e) {
        e.preventDefault();
        const submitBtn = e.target.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerText = 'Saving...';

        const payload = {
            facility_name: document.getElementById('facility_name').value,
            active_branch: document.getElementById('active_branch').value,
            maintenance_mode: document.getElementById('maintenance_mode').value,
            external_api_token: document.getElementById('external_api_token').value,
            webhook_url: document.getElementById('webhook_url').value,
            webhook_secret: document.getElementById('webhook_secret').value,
            webhook_events: document.getElementById('webhook_events').value,
            integration_fetch_url: document.getElementById('integration_fetch_url').value,
        };

        try {
            await api.post('/settings', payload);
            alert('Settings and REST API hooks updated successfully in database!');
            loadSettings();
        } catch (err) {
            alert('Failed to save settings: ' + (err.message || 'connection error'));
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Save Configuration';
        }
    }

    function generateApiKey() {
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = 'nis_msp_';
        for (let i = 0; i < 32; i++) {
            result += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        document.getElementById('external_api_token').value = result;
        updateEndpointPreview(result);
    }

    function updateEndpointPreview(token) {
        const preview = document.getElementById('incoming_endpoint_preview');
        const host = window.location.origin;
        preview.innerText = `${host}/api/external/sync-patients?api_key=${token}`;
    }

    document.getElementById('external_api_token').addEventListener('input', (e) => {
        updateEndpointPreview(e.target.value || '[TOKEN]');
    });

    function showConsole(data) {
        const wrapper = document.getElementById('test_console_wrapper');
        const consoleEl = document.getElementById('test_console');
        wrapper.classList.remove('hidden');
        consoleEl.innerText = typeof data === 'string' ? data : JSON.stringify(data, null, 4);
    }

    function clearConsole() {
        document.getElementById('test_console_wrapper').classList.add('hidden');
        document.getElementById('test_console').innerText = '{}';
    }

    async function testOutgoingFetch() {
        showConsole('Fetching external data... Please wait.');
        try {
            const res = await api.post('/settings/test-fetch');
            showConsole(res);
        } catch (err) {
            showConsole({
                success: false,
                error: err.message || 'Connection failed',
                details: 'Please ensure the registry source URL is correct and online.'
            });
        }
    }

    async function testWebhookDispatch() {
        showConsole('Dispatching real-time test event webhook... Please wait.');
        try {
            const res = await api.post('/settings/test-webhook');
            showConsole(res);
        } catch (err) {
            showConsole({
                success: false,
                error: err.message || 'Dispatch failed',
                details: 'Please ensure the Webhook destination URL is correct and accepts POST requests.'
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadSettings();
        setTimeout(() => {
            lucide.createIcons();
        }, 100);
    });
</script>
@endsection
