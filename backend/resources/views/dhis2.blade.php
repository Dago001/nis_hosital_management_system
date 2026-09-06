@extends('layouts.app')

@section('title', 'DHIS2 Export | NIS Medical Services Portal')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
            <i data-lucide="globe" class="text-emerald-600 shrink-0"></i> DHIS2 Aggregate Export
        </h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">Monthly HMIS indicators packaged as a DHIS2 dataValueSet (JSON) or CSV for national reporting.</p>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Month</label>
                <select id="d-month" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs"></select>
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Year</label>
                <input id="d-year" type="number" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs">
            </div>
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider mb-1 text-slate-700 dark:text-slate-300">Org Unit</label>
                <input id="d-org" value="NIS_MEDICAL_HQ" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-xs font-mono">
            </div>
            <button onclick="loadIndicators()" class="bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 text-white text-xs font-bold px-4 py-2.5 rounded-xl">Preview</button>
        </div>
        <div class="flex flex-wrap gap-2 mt-3">
            <button onclick="downloadExport('json')" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl flex items-center gap-1"><i data-lucide="download" class="w-4 h-4"></i> Download JSON</button>
            <button onclick="downloadExport('csv')" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl flex items-center gap-1"><i data-lucide="download" class="w-4 h-4"></i> Download CSV</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800"><h3 class="text-sm font-bold text-slate-800 dark:text-white">Indicators — <span id="ind-period">—</span></h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300"><tr>
                        <th class="py-2.5 px-4 font-bold">Data Element</th><th class="py-2.5 px-4 font-bold">Code</th>
                        <th class="py-2.5 px-4 font-bold text-right">Value</th>
                    </tr></thead>
                    <tbody id="ind-body" class="divide-y divide-slate-100 dark:divide-slate-800"><tr><td colspan="3" class="py-6 text-center text-slate-400">Click Preview.</td></tr></tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800"><h3 class="text-sm font-bold text-slate-800 dark:text-white">Top Diagnoses (this month)</h3></div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300"><tr>
                        <th class="py-2.5 px-4 font-bold">ICD-10</th><th class="py-2.5 px-4 font-bold">Diagnosis</th>
                        <th class="py-2.5 px-4 font-bold text-right">Count</th>
                    </tr></thead>
                    <tbody id="dx-body" class="divide-y divide-slate-100 dark:divide-slate-800"><tr><td colspan="3" class="py-6 text-center text-slate-400">—</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    function initPickers() {
        const now = new Date();
        const msel = document.getElementById('d-month');
        msel.innerHTML = MONTHS.map((m, i) => `<option value="${i+1}" ${i===now.getMonth()?'selected':''}>${m}</option>`).join('');
        document.getElementById('d-year').value = now.getFullYear();
    }

    function params() {
        return `year=${document.getElementById('d-year').value}&month=${document.getElementById('d-month').value}&org_unit=${encodeURIComponent(document.getElementById('d-org').value||'NIS_MEDICAL_HQ')}`;
    }

    async function loadIndicators() {
        try {
            const res = await api.get('/dhis2/indicators?' + params());
            document.getElementById('ind-period').innerText = res.period_label + ' · ' + res.org_unit;
            document.getElementById('ind-body').innerHTML = res.indicators.map(i => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="py-2.5 px-4 text-slate-800 dark:text-white">${i.label}</td>
                    <td class="py-2.5 px-4 font-mono text-slate-500">${i.code}</td>
                    <td class="py-2.5 px-4 text-right font-black text-emerald-600">${i.value}</td>
                </tr>`).join('');
            const dx = res.top_diagnoses || [];
            document.getElementById('dx-body').innerHTML = dx.length ? dx.map(d => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                    <td class="py-2.5 px-4 font-mono">${d.diagnosis_icd10||'—'}</td>
                    <td class="py-2.5 px-4">${d.diagnosis_description}</td>
                    <td class="py-2.5 px-4 text-right font-bold">${d.count}</td>
                </tr>`).join('') : '<tr><td colspan="3" class="py-6 text-center text-slate-400">No diagnoses recorded.</td></tr>';
        } catch (e) { alert(e.message || 'Failed to load indicators.'); }
    }

    async function downloadExport(kind) {
        const token = localStorage.getItem('nis_hms_token');
        const ep = kind === 'csv' ? 'export.csv' : 'export';
        try {
            const res = await fetch(`${api.baseUrl}/dhis2/${ep}?` + params(), { headers: { 'Authorization': `Bearer ${token}`, 'Accept': kind === 'csv' ? 'text/csv' : 'application/json' } });
            if (!res.ok) throw new Error('Export failed');
            let blob, name;
            const period = `${document.getElementById('d-year').value}${String(document.getElementById('d-month').value).padStart(2,'0')}`;
            if (kind === 'csv') {
                blob = await res.blob(); name = `dhis2_export_${period}.csv`;
            } else {
                const json = await res.json();
                blob = new Blob([JSON.stringify(json, null, 2)], { type: 'application/json' });
                name = `dhis2_export_${period}.json`;
            }
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a'); a.href = url; a.download = name;
            document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
        } catch (e) { alert('Could not download the export.'); }
    }

    document.addEventListener('DOMContentLoaded', () => { initPickers(); loadIndicators(); });
</script>
@endsection
