<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Portal - NIS Medical Services</title>
    <link rel="icon" type="image/jpeg" href="/images/nis_logo.jpg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans bg-slate-50 text-slate-800">
    <div class="min-h-full flex flex-col">
        <header class="bg-emerald-800 text-white">
            <div class="max-w-3xl mx-auto px-4 py-4 flex items-center gap-3">
                <img src="/images/nis_logo.jpg" class="w-9 h-9 rounded-full bg-white/10" alt="NIS">
                <div>
                    <div class="text-sm font-black uppercase tracking-wide leading-tight">Nigeria Immigration Service</div>
                    <div class="text-[11px] opacity-90">Medical Services — Patient Portal</div>
                </div>
            </div>
        </header>

        <main class="flex-1 max-w-3xl w-full mx-auto px-4 py-6 space-y-6">
            <!-- Lookup card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <h1 class="text-lg font-black text-slate-800">Check your records</h1>
                <p class="text-xs text-slate-500 mb-4">Enter your hospital code and surname to view your upcoming appointments and prescriptions.</p>
                <form onsubmit="lookup(event)" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input id="p-code" required placeholder="Hospital code (e.g. NIS/PAT/123456)" class="sm:col-span-2 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <input id="p-surname" required placeholder="Surname" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <button type="submit" class="sm:col-span-3 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold py-2.5 rounded-xl transition">View my records</button>
                </form>
                <div id="p-error" class="hidden mt-3 text-xs font-semibold rounded-xl px-3 py-2 bg-red-50 text-red-700"></div>
            </div>

            <!-- Results -->
            <div id="p-results" class="hidden space-y-5">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <div class="text-xs text-slate-500">Signed in as</div>
                    <div class="text-lg font-black text-slate-800" id="r-name">—</div>
                    <div class="text-xs font-mono text-emerald-700" id="r-code">—</div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h2 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">📅 Upcoming Appointments</h2>
                    <div id="r-appts" class="space-y-2 text-sm"></div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h2 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">💊 Recent Prescriptions</h2>
                    <div id="r-rx" class="space-y-3 text-sm"></div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h2 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">🩺 Recent Visits</h2>
                    <div id="r-visits" class="space-y-2 text-sm"></div>
                </div>

                <p class="text-[10px] text-slate-400 text-center">For medical advice, please contact your NIS medical facility. This portal shows a limited summary only.</p>
            </div>
        </main>

        <footer class="text-center text-[10px] text-slate-400 py-4">
            &copy; <span id="yr"></span> NIS Medical Services · <a href="/login" class="text-emerald-600 hover:underline">Staff login</a>
        </footer>
    </div>

    <script>
        document.getElementById('yr').textContent = new Date().getFullYear();
        const apiBase = (() => {
            if (window.location.port === '5173' || window.location.port === '5174') return 'http://localhost:8000/api';
            const idx = window.location.pathname.indexOf('/public');
            if (idx !== -1) return window.location.pathname.substring(0, idx + 7) + '/api';
            return '/api';
        })();

        async function lookup(e) {
            e.preventDefault();
            const err = document.getElementById('p-error');
            err.classList.add('hidden');
            try {
                const res = await fetch(apiBase + '/portal/lookup', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        hospital_code: document.getElementById('p-code').value.trim(),
                        surname: document.getElementById('p-surname').value.trim(),
                    }),
                });
                const data = await res.json();
                if (!res.ok || !data.found) throw new Error(data.message || 'Not found.');
                render(data);
            } catch (ex) {
                err.textContent = ex.message || 'Lookup failed. Please check your details.';
                err.classList.remove('hidden');
                document.getElementById('p-results').classList.add('hidden');
            }
        }

        function render(d) {
            document.getElementById('r-name').textContent = d.patient.name;
            document.getElementById('r-code').textContent = d.patient.hospital_code || '';

            document.getElementById('r-appts').innerHTML = (d.appointments || []).length
                ? d.appointments.map(a => `<div class="flex justify-between items-center border border-slate-100 rounded-xl px-3 py-2">
                    <div><b class="text-slate-800">${a.date} ${a.time || ''}</b><div class="text-xs text-slate-500">${a.department || 'Outpatient'}${a.doctor ? ' · Dr. '+a.doctor : ''}</div></div>
                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">${a.status.replace('_',' ')}</span></div>`).join('')
                : '<p class="text-slate-400 text-xs">No upcoming appointments.</p>';

            document.getElementById('r-rx').innerHTML = (d.prescriptions || []).length
                ? d.prescriptions.map(p => `<div class="border border-slate-100 rounded-xl p-3">
                    <div class="text-xs text-slate-500 mb-1">${p.date} · ${p.status}</div>
                    ${p.items.map(it => `<div class="text-xs text-slate-700">• <b>${it.drug_name}</b> ${it.dosage||''} ${it.frequency||''} <span class="text-slate-400">(${it.status})</span></div>`).join('')}
                </div>`).join('')
                : '<p class="text-slate-400 text-xs">No prescriptions on record.</p>';

            document.getElementById('r-visits').innerHTML = (d.visits || []).length
                ? d.visits.map(v => `<div class="flex justify-between border border-slate-100 rounded-xl px-3 py-2"><b class="text-slate-800">${v.date}</b><span class="text-xs text-slate-500">${v.department || '—'}</span></div>`).join('')
                : '<p class="text-slate-400 text-xs">No recent visits.</p>';

            document.getElementById('p-results').classList.remove('hidden');
        }
    </script>
</body>
</html>
