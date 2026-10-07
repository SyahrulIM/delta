{{-- resources/views/trading/report.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laporan DSI – Trading Division 2026</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <style>
        :root {
            --bg-page:           #f0f2f5;
            --bg-card:           #ffffff;
            --border-card:       #e2e8f0;
            --shadow-card:       0 2px 12px rgba(0,0,0,.08);
            --color-kontrak:     #2563eb;
            --color-payment:     #1a1a1a;
            --color-po-supplier: #dc2626;
            --color-invoice:     #16a34a;
            --radius:            12px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: var(--bg-page);
            color: #1e293b;
            min-height: 100vh;
            padding: 2rem 1.5rem;
        }

        /* ─── Header ─────────────────────────────────────────────── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .header-left { display: flex; align-items: center; gap: 1rem; }
        .brand-badge {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
            color: #fff;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: .35rem .8rem;
            border-radius: 6px;
        }
        .page-title    { font-size: 1.4rem; font-weight: 700; color: #1e293b; }
        .page-subtitle { font-size: .85rem; color: #64748b; margin-top: .2rem; }

        /* ─── Export button ──────────────────────────────────────── */
        .btn-export {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #dc2626;
            color: #fff;
            font-size: .85rem;
            font-weight: 700;
            padding: .6rem 1.2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background .18s, transform .1s, box-shadow .18s;
            box-shadow: 0 2px 8px rgba(220,38,38,.3);
        }
        .btn-export:hover   { background: #b91c1c; box-shadow: 0 4px 14px rgba(220,38,38,.4); }
        .btn-export:active  { transform: scale(.97); }
        .btn-export:disabled { background: #94a3b8; box-shadow: none; cursor: not-allowed; }
        .btn-export svg     { width: 16px; height: 16px; flex-shrink: 0; }
        .spinner {
            display: none;
            width: 15px; height: 15px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ─── Legend ─────────────────────────────────────────────── */
        .legend-strip {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem 1.5rem;
            margin-bottom: 1.75rem;
            padding: .75rem 1rem;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
        }
        .legend-item { display: flex; align-items: center; gap: .45rem; font-size: .82rem; font-weight: 600; color: #334155; }
        .legend-dot  { width: 14px; height: 14px; border-radius: 3px; flex-shrink: 0; }

        /* ─── Cards ──────────────────────────────────────────────── */
        .charts-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        @media (min-width: 1100px) { .charts-grid { grid-template-columns: 1fr 1fr; } }

        .chart-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
            padding: 1.5rem 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .chart-card-title  { font-size: 1rem; font-weight: 700; color: #1e293b; }
        .chart-card-period { font-size: .78rem; color: #94a3b8; font-weight: 500; }
        .chart-wrapper     { position: relative; height: 380px; }

        /* ─── Summary table ──────────────────────────────────────── */
        .summary-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
            padding: 1.5rem;
            margin-top: 1.5rem;
            overflow-x: auto;
        }
        .summary-card h3 { font-size: .95rem; font-weight: 700; margin-bottom: 1rem; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; font-size: .82rem; }
        thead tr { background: #f8fafc; }
        th { padding: .6rem .9rem; text-align: left; font-weight: 700; color: #475569; border-bottom: 2px solid #e2e8f0; white-space: nowrap; }
        td { padding: .55rem .9rem; color: #334155; border-bottom: 1px solid #f1f5f9; white-space: nowrap; }
        tr:hover td { background: #f8fafc; }
        .td-amount { text-align: right; font-variant-numeric: tabular-nums; }

        /* ─── Toast ──────────────────────────────────────────────── */
        #toast {
            position: fixed; bottom: 1.5rem; right: 1.5rem;
            background: #1e293b; color: #fff;
            padding: .75rem 1.2rem; border-radius: 8px;
            font-size: .85rem; font-weight: 500;
            box-shadow: 0 4px 16px rgba(0,0,0,.2);
            opacity: 0; transform: translateY(10px);
            transition: opacity .3s, transform .3s;
            pointer-events: none; z-index: 9999;
        }
        #toast.show       { opacity: 1; transform: translateY(0); }
        #toast.show.error { background: #dc2626; }

        .filter-bar {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: .75rem 1.25rem;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow-card);
            padding: .85rem 1.1rem;
            margin-bottom: 1.5rem;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: .5rem;
        }
        .filter-group label {
            font-size: .8rem;
            font-weight: 600;
            color: #64748b;
            white-space: nowrap;
        }
        .filter-group select {
            font-size: .83rem;
            font-weight: 600;
            color: #1e293b;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: .35rem .6rem;
            background: #f8fafc;
            cursor: pointer;
            transition: border-color .15s;
        }
        .filter-group select:focus {
            outline: none;
            border-color: #2563eb;
        }
        .filter-badge {
            margin-left: auto;
            font-size: .82rem;
            color: #64748b;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px;
            padding: .35rem .75rem;
        }
    </style>
</head>
<body>

{{-- ── Page Header ─────────────────────────────────────────────────── --}}
<header class="page-header">
    <div class="header-left">
        <span class="brand-badge">Trading Division</span>
        <div>
            <div class="page-title">Laporan Kontrak Divisi Trading 2026</div>
            <div class="page-subtitle">PT Delta Systech Indonesia &mdash; Periode s/d 31 Maret 2026</div>
        </div>
    </div>

    <button class="btn-export" id="btnExportPdf" onclick="exportToPdf()">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
        </svg>
        <span id="btnLabel">Export PDF</span>
        <span class="spinner" id="spinner"></span>
    </button>
</header>

{{-- ── Filter Periode ─────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('report.index') }}" id="filterForm">
<div class="filter-bar">
    <div class="filter-group">
        <label>Tahun</label>
        <select name="year" onchange="this.form.submit()">
            @foreach(range(now()->year, now()->year - 3) as $y)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
    </div>

    <div class="filter-group">
        <label>Dari Bulan</label>
        <select name="month_from" onchange="this.form.submit()">
            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $name)
                <option value="{{ $i + 1 }}" {{ $monthFrom == $i + 1 ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div class="filter-group">
        <label>Sampai Bulan</label>
        <select name="month_to" onchange="this.form.submit()">
            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $name)
                <option value="{{ $i + 1 }}" {{ $monthTo == $i + 1 ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        </select>
    </div>

    <div class="filter-badge">
        Periode: <strong>{{ $monthNames }}</strong>
    </div>
</div>
</form>

{{-- ── Shared Legend ────────────────────────────────────────────────── --}}
<div class="legend-strip">
    <div class="legend-item"><span class="legend-dot" style="background:var(--color-kontrak)"></span> Kontrak</div>
    <div class="legend-item"><span class="legend-dot" style="background:var(--color-payment)"></span> Payment</div>
    <div class="legend-item"><span class="legend-dot" style="background:var(--color-po-supplier)"></span> PO Supplier</div>
    <div class="legend-item"><span class="legend-dot" style="background:var(--color-invoice)"></span> Invoice</div>
</div>

{{-- ── Charts Grid ─────────────────────────────────────────────────── --}}
<div class="charts-grid">
    <div class="chart-card">
        <div>
            <div class="chart-card-title">Akumulasi Kontrak</div>
            <div class="chart-card-period">Kumulatif s/d 31 Maret 2026</div>
        </div>
        <div class="chart-wrapper"><canvas id="chartAkumulasi"></canvas></div>
    </div>
    <div class="chart-card">
        <div>
            <div class="chart-card-title">Rekapitulasi Kontrak</div>
            <div class="chart-card-period">Nilai per Periode s/d 31 Maret 2026</div>
        </div>
        <div class="chart-wrapper"><canvas id="chartRekap"></canvas></div>
    </div>
</div>

{{-- ── Summary Table ────────────────────────────────────────────────── --}}
<div class="summary-card">
    <h3>Ringkasan Akumulasi s/d 31 Maret 2026</h3>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                @foreach($labels as $lbl)
                    <th style="text-align:right">{{ $lbl }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $series = [
                    'Kontrak'     => ['key' => 'kontrak',     'color' => '#2563eb'],
                    'Payment'     => ['key' => 'payment',     'color' => '#1a1a1a'],
                    'PO Supplier' => ['key' => 'po_supplier', 'color' => '#dc2626'],
                    'Invoice'     => ['key' => 'invoice',     'color' => '#16a34a'],
                ];
            @endphp
            @foreach($series as $name => $cfg)
            <tr>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:.45rem;">
                        <span style="width:12px;height:12px;background:{{ $cfg['color'] }};border-radius:3px;display:inline-block;flex-shrink:0;"></span>
                        {{ $name }}
                    </span>
                </td>
                @foreach($akumulasiData[$cfg['key']] as $val)
                    <td class="td-amount">{{ $val > 0 ? 'Rp '.number_format($val, 0, ',', '.') : 'Rp -' }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="summary-card">
    <h3>Ringkasan Rekapitulasi s/d 31 Maret 2026</h3>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                @foreach($labels as $lbl)
                    <th style="text-align:right">{{ $lbl }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $series = [
                    'Kontrak'     => ['key' => 'kontrak',     'color' => '#2563eb'],
                    'Payment'     => ['key' => 'payment',     'color' => '#1a1a1a'],
                    'PO Supplier' => ['key' => 'po_supplier', 'color' => '#dc2626'],
                    'Invoice'     => ['key' => 'invoice',     'color' => '#16a34a'],
                ];
            @endphp
            @foreach($series as $name => $cfg)
            <tr>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:.45rem;">
                        <span style="width:12px;height:12px;background:{{ $cfg['color'] }};border-radius:3px;display:inline-block;flex-shrink:0;"></span>
                        {{ $name }}
                    </span>
                </td>
                @foreach($rekapData[$cfg['key']] as $val)
                    <td class="td-amount">{{ $val > 0 ? 'Rp '.number_format($val, 0, ',', '.') : 'Rp -' }}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- ── Toast ────────────────────────────────────────────────────────── --}}
<div id="toast"></div>

{{-- ── JavaScript ──────────────────────────────────────────────────── --}}
<script>
Chart.register(ChartDataLabels);

const LABELS = @json($labels);
const COLORS = {
    kontrak: '#2563eb', payment: '#1a1a1a',
    poSupplier: '#dc2626', invoice: '#16a34a',
};

function rupiahShort(val) {
    if (val >= 1e9) return 'Rp' + (val / 1e9).toFixed(2).replace('.', ',') + 'M';
    if (val >= 1e6) return 'Rp' + (val / 1e6).toFixed(1).replace('.', ',') + 'jt';
    return 'Rp' + val.toLocaleString('id-ID');
}
const tooltipCallbacks = {
    label: ctx => ' ' + ctx.dataset.label + ': Rp ' + Number(ctx.raw).toLocaleString('id-ID')
};

// ── Line Chart – Akumulasi ────────────────────────────────────────────
const akumulasiData = @json($akumulasiData);

const chartAkumulasi = new Chart(document.getElementById('chartAkumulasi'), {
    type: 'line',
    data: {
        labels: LABELS,
        datasets: [
            { label: 'Kontrak',     data: akumulasiData.kontrak,     borderColor: COLORS.kontrak,     backgroundColor: COLORS.kontrak     + '18', borderWidth: 2.5, pointRadius: 5, pointHoverRadius: 7, fill: false, tension: 0 },
            { label: 'Payment',     data: akumulasiData.payment,     borderColor: COLORS.payment,     backgroundColor: COLORS.payment     + '18', borderWidth: 2.5, pointRadius: 5, pointHoverRadius: 7, fill: false, tension: 0 },
            { label: 'PO Supplier', data: akumulasiData.po_supplier, borderColor: COLORS.poSupplier, backgroundColor: COLORS.poSupplier + '18', borderWidth: 2.5, pointRadius: 5, pointHoverRadius: 7, fill: false, tension: 0 },
            { label: 'Invoice',     data: akumulasiData.invoice,     borderColor: COLORS.invoice,     backgroundColor: COLORS.invoice     + '18', borderWidth: 2.5, pointRadius: 5, pointHoverRadius: 7, fill: false, tension: 0 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        animation: { duration: 800 },
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: tooltipCallbacks },
            datalabels: { display: false },
        },
        scales: {
            x: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11, weight: '600' } } },
            y: { grid: { color: '#f1f5f9' }, beginAtZero: true, ticks: { font: { size: 10 }, callback: rupiahShort } }
        }
    }
});

// ── Bar Chart – Rekapitulasi ──────────────────────────────────────────
const rekapData = @json($rekapData);

const chartRekap = new Chart(document.getElementById('chartRekap'), {
    type: 'bar',
    data: {
        labels: LABELS,
        datasets: [
            { label: 'Kontrak',     data: rekapData.kontrak,     backgroundColor: COLORS.kontrak     + 'cc', borderColor: COLORS.kontrak,     borderWidth: 1.5, borderRadius: 4 },
            { label: 'Payment',     data: rekapData.payment,     backgroundColor: COLORS.payment     + 'cc', borderColor: COLORS.payment,     borderWidth: 1.5, borderRadius: 4 },
            { label: 'PO Supplier', data: rekapData.po_supplier, backgroundColor: COLORS.poSupplier + 'cc', borderColor: COLORS.poSupplier, borderWidth: 1.5, borderRadius: 4 },
            { label: 'Invoice',     data: rekapData.invoice,     backgroundColor: COLORS.invoice     + 'cc', borderColor: COLORS.invoice,     borderWidth: 1.5, borderRadius: 4 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        animation: { duration: 800 },
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: tooltipCallbacks },
            datalabels: { display: false },
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' } } },
            y: { grid: { color: '#f1f5f9' }, beginAtZero: true, ticks: { font: { size: 10 }, callback: rupiahShort } }
        }
    }
});

// ── Toast helper ──────────────────────────────────────────────────────
function showToast(msg, isError = false) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'show' + (isError ? ' error' : '');
    setTimeout(() => { t.className = ''; }, 3500);
}

// ── Export to PDF ─────────────────────────────────────────────────────
// Alur:
//  1. Capture canvas kedua chart → base64 PNG
//  2. POST ke Laravel (/trading/report/export-pdf)
//  3. DomPDF render Blade → kirim balik binary PDF
//  4. Browser trigger download otomatis
async function exportToPdf() {
    const btn     = document.getElementById('btnExportPdf');
    const label   = document.getElementById('btnLabel');
    const spinner = document.getElementById('spinner');

    btn.disabled          = true;
    label.textContent     = 'Menyiapkan…';
    spinner.style.display = 'block';

    try {
        // Beri waktu 300ms agar animasi chart benar-benar selesai
        await new Promise(r => setTimeout(r, 300));

        const imgAkumulasi = chartAkumulasi.toBase64Image('image/png', 0.85);
        const imgRekap     = chartRekap.toBase64Image('image/png', 0.85);

        const response = await fetch('{{ route("report.export-pdf") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept':       'application/pdf',
            },
            // Di dalam fungsi exportToPdf(), tambahkan year/month ke body POST:
            body: JSON.stringify({
                chart_akumulasi: imgAkumulasi,
                chart_rekap:     imgRekap,
                year:            {{ $year }},
                month_from:      {{ $monthFrom }},
                month_to:        {{ $monthTo }},
            }),
        });

        if (!response.ok) {
            const err = await response.json().catch(() => ({}));
            throw new Error(err.message ?? 'Server error ' + response.status);
        }

        // Trigger browser download
        const blob     = await response.blob();
        const url      = URL.createObjectURL(blob);
        const anchor   = document.createElement('a');
        anchor.href    = url;
        anchor.download = 'Laporan_Trading_DSI_' + new Date().toISOString().slice(0,10) + '.pdf';
        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();
        URL.revokeObjectURL(url);

        showToast('✓ PDF berhasil diunduh');
    } catch (err) {
        console.error(err);
        showToast('Gagal export PDF: ' + err.message, true);
    } finally {
        btn.disabled          = false;
        label.textContent     = 'Export PDF';
        spinner.style.display = 'none';
    }
}
</script>
</body>
</html>
