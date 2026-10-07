{{-- resources/views/trading/report-pdf.blade.php --}}
{{-- Dirender server-side oleh DomPDF (landscape A4) --}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    /* ─── Reset & page ───────────────────────────────────────── */
    * { box-sizing: border-box; margin: 0; padding: 0; }

    @page {
        size: A4 landscape;
        margin: 14mm 16mm 14mm 16mm;
    }

    body {
        font-family: sans-serif;
        font-size: 9pt;
        color: #1e293b;
        background: #ffffff;
        margin: 4mm 6mm 4mm 6mm;
    }

    /* ─── Header ─────────────────────────────────────────────── */
    .header {
        display: table;
        width: 100%;
        border-bottom: 2.5pt solid #2563eb;
        padding-bottom: 6pt;
        margin-bottom: 10pt;
    }
    .header-left  { display: table-cell; vertical-align: middle; width: 70%; }
    .header-right { display: table-cell; vertical-align: middle; text-align: right; width: 30%; }

    .brand-badge {
        display: inline-block;
        background: #2563eb;
        color: #fff;
        font-size: 7pt;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        padding: 2pt 6pt;
        border-radius: 3pt;
        margin-bottom: 3pt;
    }
    .page-title    { font-size: 13pt; font-weight: 700; color: #1e293b; }
    .page-subtitle { font-size: 8pt; color: #64748b; margin-top: 2pt; }
    .export-info   { font-size: 7.5pt; color: #94a3b8; }

    /* ─── Legend ─────────────────────────────────────────────── */
    .legend {
        display: table;
        width: 100%;
        border: 0.5pt solid #e2e8f0;
        border-radius: 4pt;
        background: #f8fafc;
        padding: 3pt 8pt;
        margin-bottom: 10pt;
    }
    .legend-item {
        display: inline-block;
        margin-right: 16pt;
        font-size: 8pt;
        font-weight: 600;
        color: #334155;
        vertical-align: middle;
    }
    .legend-dot {
        display: inline-block;
        width: 10pt;
        height: 10pt;
        border-radius: 2pt;
        margin-right: 4pt;
        vertical-align: middle;
    }

    /* ─── Charts row ─────────────────────────────────────────── */
    .charts-row {
        display: table;
        width: 100%;
        margin-bottom: 10pt;
        border-spacing: 8pt 0;
    }
    .chart-cell {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        border: 0.5pt solid #e2e8f0;
        border-radius: 6pt;
        padding: 8pt;
        background: #ffffff;
    }
    .chart-cell-title  { font-size: 9pt; font-weight: 700; color: #1e293b; margin-bottom: 2pt; }
    .chart-cell-period { font-size: 7.5pt; color: #94a3b8; margin-bottom: 6pt; }
    .chart-cell img    { width: 80%; height: auto; display: block; }

    /* ─── Summary table ──────────────────────────────────────── */
    .section-title {
        font-size: 9pt;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 5pt;
        padding-bottom: 3pt;
        border-bottom: 1pt solid #e2e8f0;
    }

    table.summary {
        width: 100%;
        border-collapse: collapse;
        font-size: 7.5pt;
    }
    table.summary thead tr { background: #f1f5f9; }
    table.summary th {
        padding: 2pt 4pt;
        text-align: left;
        font-weight: 700;
        color: #475569;
        border-bottom: 1.5pt solid #cbd5e1;
        white-space: nowrap;
    }
    table.summary th.num { text-align: right; }
    table.summary td {
        padding: 3.5pt 6pt;
        color: #334155;
        border-bottom: 0.5pt solid #f1f5f9;
        white-space: nowrap;
    }
    table.summary td.num { text-align: right; font-variant-numeric: tabular-nums; }
    table.summary tr:nth-child(even) td { background: #f8fafc; }

    /* ─── Footer ─────────────────────────────────────────────── */
    .footer {
        margin-top: 8pt;
        padding-top: 5pt;
        border-top: 0.5pt solid #e2e8f0;
        font-size: 7pt;
        color: #94a3b8;
        text-align: center;
    }
</style>
</head>
<body>

{{-- ── Header ──────────────────────────────────────────────────────── --}}
<div class="header">
    <div class="header-left">
        <div class="brand-badge">Trading Division</div>
        <div class="page-title">Laporan Kontrak Divisi Trading 2026</div>
        <div class="page-subtitle">PT Delta Systech Indonesia &mdash; Periode s/d 31 Maret 2026</div>
    </div>
    <div class="header-right">
        <div class="export-info">Dicetak: {{ $exportedAt }}</div>
    </div>
</div>

{{-- ── Legend ──────────────────────────────────────────────────────── --}}
<div class="legend">
    <span class="legend-item"><span class="legend-dot" style="background:#2563eb;"></span>Kontrak</span>
    <span class="legend-item"><span class="legend-dot" style="background:#1a1a1a;"></span>Payment</span>
    <span class="legend-item"><span class="legend-dot" style="background:#dc2626;"></span>PO Supplier</span>
    <span class="legend-item"><span class="legend-dot" style="background:#16a34a;"></span>Invoice</span>
</div>

{{-- ── Charts ───────────────────────────────────────────────────────── --}}
<div class="charts-row">
    <div class="chart-cell">
        <div class="chart-cell-title">Akumulasi Kontrak</div>
        <div class="chart-cell-period">Kumulatif s/d 31 Maret 2026</div>
        <img src="{{ $chartAkumulasiImg }}" alt="Grafik Akumulasi">
    </div>
    <div class="chart-cell" style="margin-left:8pt;">
        <div class="chart-cell-title">Rekapitulasi Kontrak</div>
        <div class="chart-cell-period">Nilai per Periode s/d 31 Maret 2026</div>
        <img src="{{ $chartRekapImg }}" alt="Grafik Rekapitulasi">
    </div>
</div>

{{-- ── Summary Table ────────────────────────────────────────────────── --}}
<div class="section-title">Ringkasan Akumulasi s/d 31 Maret 2026</div>
<table class="summary">
    <thead>
        <tr>
            <th>Kategori</th>
            @foreach($labels as $lbl)
                <th class="num">{{ $lbl }}</th>
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
                <span style="display:inline-block;width:9pt;height:9pt;background:{{ $cfg['color'] }};border-radius:2pt;vertical-align:middle;margin-right:4pt;"></span>
                <strong>{{ $name }}</strong>
            </td>
            @foreach($akumulasiData[$cfg['key']] as $val)
                <td class="num">{{ $val > 0 ? 'Rp '.number_format($val, 0, ',', '.') : 'Rp -' }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>

<div class="section-title">Ringkasan Rekapitulasi s/d 31 Maret 2026</div>
<table class="summary">
    <thead>
        <tr>
            <th>Kategori</th>
            @foreach($labels as $lbl)
                <th class="num">{{ $lbl }}</th>
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
                <span style="display:inline-block;width:9pt;height:9pt;background:{{ $cfg['color'] }};border-radius:2pt;vertical-align:middle;margin-right:4pt;"></span>
                <strong>{{ $name }}</strong>
            </td>
            @foreach($rekapData[$cfg['key']] as $val)
                <td class="num">{{ $val > 0 ? 'Rp '.number_format($val, 0, ',', '.') : 'Rp -' }}</td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ── Footer ──────────────────────────────────────────────────────── --}}
<div class="footer">
    PT Delta Systech Indonesia &bull; Laporan Divisi Trading 2026 &bull; Dokumen ini digenerate otomatis
</div>

</body>
</html>
