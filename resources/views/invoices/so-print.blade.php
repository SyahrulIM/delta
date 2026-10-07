<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Order Invoice {{ $inv->invoice_number }}</title>

<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 20px; color: #000; }
    table { width: 100%; border-collapse: collapse; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .mt-2 { margin-top: 10px; }
    .mt-3 { margin-top: 20px; }
    .small { font-size: 11px; }
    th, td { padding: 6px; vertical-align: top; }
    .bordered th, .bordered td { border: 1px solid #111; }
    hr { border: 0; border-top: 1px solid #555; margin: 10px 0; }
</style>
</head>

<body>

{{-- HEADER --}}
<table>
    <tr>
        <td style="width: 60%;">
            <div style="font-size: 12px; margin-top: 5px;">
                {{ $inv->source->project->contact_name }} <br>
                {{ $inv->source->project->location }} <br>
                {{ $inv->source->project->pic }} <br>
            </div>
        </td>

        {{-- <td class="text-right" style="width: 40%;">
            <table>
                <tr>
                    <td class="small">Nomor</td>
                    <td class="small">: {{ $inv->spm_number ?? $inv->so_number }}</td>
                </tr>
                <tr>
                    <td class="small">Tanggal</td>
                    <td class="small">: {{ \Carbon\Carbon::parse($inv->so_date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="small">Beban Proyek</td>
                    <td class="small">: {{ $inv->project_name }}</td>
                </tr>
                <tr>
                    <td class="small">ID Proyek</td>
                    <td class="small">: {{ $inv->project_id }}</td>
                </tr>
            </table>
        </td> --}}
    </tr>
</table>

<div class="text-center" style="font-size: 18px; font-weight: bold;">Invoice</div>
<hr>

{{-- SUPPLIER --}}
<table class="no-border">
    <tr>
        <td style="width: 20%;"><strong>Job No.</strong></td>
        <td style="width: 30%;">: {{ $inv->source->job }}</td>

        <td><strong>Invoice No.</strong></td>
        <td>: {{ $inv->invoice_number }}</td>
    </tr>
    <tr>
        <td><strong>Contract No.</strong></td>
        <td>: {{ $inv->source->contract_no }}</td>
        <td><strong>Invoice Date</strong></td>
        <td>: {{ $inv->invoice_date }}</td>
    </tr>
    <tr>
        <td><strong>Date</strong></td>
        <td>: {{ $inv->source->so_date }}</td>
    </tr>
    <tr>
        <td><strong>Contract Value</strong></td>
        <td>: {{ number_format($inv->subtotal, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td><strong>Project</strong></td>
        <td>: {{ $inv->source->project->name }}</td>
    </tr>
</table>

<br>

{{-- <div class="small">
    Bersama ini kami pesan material dengan spesifikasi sebagai berikut:
</div> --}}

<br>

{{-- ITEMS TABLE --}}
<table class="bordered">
    <thead>
        <tr>
            <th style="width: 5%;">No</th>
            <th style="width: 45%;">Nama Material</th>
            <th style="width: 10%;" class="text-center">Qty</th>
            <th style="width: 10%;" class="text-center">Satuan</th>
            <th style="width: 15%;" class="text-right">Harga Satuan (Rp)</th>
            <th style="width: 15%;" class="text-right">Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($inv->items as $i => $item)
        <tr>
            <td class="text-center">{{ $i+1 }}</td>
            <td>{{ $item->sourceItem->product->name }}</td>
            <td class="text-center">{{ number_format($item->qty, 0) }}</td>
            <td class="text-center">{{ $item->unit ?? 'LTR' }}</td>
            <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($item->total, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<br>

{{-- TOTALS --}}
<table style="width: 40%; float: right;">
    <tr>
        <td>Jumlah</td>
        <td class="text-right">{{ number_format($inv->subtotal, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td>PPN 11%</td>
        <td class="text-right">{{ number_format($inv->tax, 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td><strong>Jumlah Total</strong></td>
        <td class="text-right"><strong>{{ number_format($inv->grand_total, 0, ',', '.') }}</strong></td>
    </tr>
</table>

<div style="clear: both;"></div>
<br>

{{-- TERBILANG --}}
<div>
    <strong>Terbilang:</strong><br>
    <i>
        {{ ucwords(terbilang($inv->grand_total)) }} rupiah
    </i>
</div>

<br>

{{-- SYARAT SYARAT --}}
<div class="small">
    <strong>Syarat-syarat:</strong><br>
    {{-- {!! nl2br(e($inv->notes)) !!} --}}
</div>

<br><br>

{{-- SIGNATURE --}}
<table style="width: 100%; margin-top: 30px;">
    <tr>
        {{-- <td class="text-center" style="width: 33%;">
            Dibuat oleh:<br><br><br><br>
            _______________________<br>
            Admin Proyek
        </td>

        <td class="text-center" style="width: 33%;">
            Diperiksa oleh:<br><br><br><br>
            _______________________<br>
            Site Manager
        </td> --}}
<td></td>
<td></td>
        <td class="text-center" style="width: 33%;">
            Disetujui oleh:<br><br><br><br><br>
            Rizal Ghozali <br>
            _______________________<br>
            Branch Manager
        </td>
    </tr>
</table>

<br>

{{-- <div class="text-center small">
    PT WASKITA KARYA (PERSERO) TBK — NPWP 01.002.331.7-051.000
</div> --}}

</body>
</html>
