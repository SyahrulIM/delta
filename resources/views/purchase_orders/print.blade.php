<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>PO {{ $po->po_number }}</title>

<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #111;
        margin: 60px 20px 110px 20px; /* ruang header & footer */
    }

    table { width:100%; border-collapse: collapse; }
    th, td { padding:6px; vertical-align: top; }

    .no-border td, .no-border th { border: none !important; padding:2px; }
    .table-border td, .table-border th { border: 1px solid #333; padding:6px; }

    .text-right { text-align:right; }
    .text-center { text-align:center; }
    .small { font-size:11px; }
    .muted { color:#666; }
    .h1 { font-size:18px; font-weight:bold; }

    hr { border:none; border-top:1px solid #ddd; margin:10px 0; }

    /* HEADER & FOOTER */
    header {
        position: fixed;
        top: -20px;
        left: 0;
        right: 0;
        height: 20px;
    }

    footer {
        position: fixed;
        bottom: -20px;
        left: 0;
        right: 0;
        height: 50px;
    }

    header img,
    footer img {
        width: 100%;
    }
</style>
</head>

<body>

{{-- HEADER --}}
<header>
    <img src="{{ public_path('header.jpg') }}">
</header>

{{-- TITLE --}}
<table class="no-border">
    <tr>
        <td width="60%">
            <div class="h1">PURCHASE ORDER</div>
            <table class="no-border" align="right">
                <tr>
                    <td width="25%" class="small">Date</td>
                    <td width="5%">:</td>
                    <td class="small">{{ \Carbon\Carbon::parse($po->po_date)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td width="25%" class="small">Project Name</td>
                    <td width="5%">:</td>
                    <td style="color: red" class="small">{{ $po->project->name }}</td>
                </tr>
                <tr>
                    <td width="25%" class="small">Contract No.</td>
                    <td width="5%">:</td>
                    <td class="small">{{ $po->contract_no }}</td>
                </tr>
                <tr>
                    <td width="25%" class="small">Job</td>
                    <td width="5%">:</td>
                    <td class="small">{{ $po->job }}</td>
                </tr>
                <tr>
                    <td width="25%" class="small">Order No.</td>
                    <td width="5%">:</td>
                    <td class="small"><strong>{{ $po->po_number }}</strong></td>
                </tr>
                <tr>
                    <td width="25%" class="small">PR No.</td>
                    <td width="5%">:</td>
                    <td class="small">{{ $po->purchaseRequest->pr_number }}</td>
                </tr>
                <tr>
                    <td width="25%" class="small">To.</td>
                    <td width="5%">:</td>
                    <td class="small">{{ $po->supplier->name }}</td>
                </tr>
                <tr><td width="25%">&nbsp;</td></tr>
                <tr>
                    <td width="25%" class="small">Telp.</td>
                    <td width="5%">:</td>
                    <td class="small">{{ $po->supplier->phone }}</td>
                </tr>
                <tr>
                    <td width="25%" class="small">Fax.</td>
                    <td width="5%">:</td>
                    <td class="small">{{ $po->supplier->fax }}</td>
                </tr>
                <tr>
                    <td width="25%" class="small">Attn.</td>
                    <td width="5%">:</td>
                    <td class="small">{{ $po->supplier->pic }}</td>
                </tr>

            </table>
        </td>
        <td width="25%" class="text-right">
            <table class="no-border" align="right">
                <tr><td class="small" style="color: red">PT. DELTA SYSTECH INDONESIA</td></tr>
                <tr><td class="small">Wisma Ritra 2nd Floor</td></tr>
                <tr><td class="small">Jl. Warung Buncit Raya No. 6</td></tr>
                <tr><td class="small">Jakarta 12740</td></tr>
                <tr><td class="small">P: +62 21 797 0825</td></tr>
                <tr><td class="small">F: +62 21 797 0825</td></tr>
                <td>&nbsp;</td>
                <tr><td class="small">Surabaya Branch</td></tr>
                <tr><td class="small">Ruko Sentraland Blok B-36</td></tr>
                <tr><td class="small">Kota Baru Driyorejo Gresik</td></tr>
                <tr><td class="small">Jawa Timur</td></tr>
                <tr><td class="small">P: +62 813 2300 3393</td></tr>
            </table>
        </td>
    </tr>
</table>

<hr>

{{-- SUPPLIER --}}
{{-- <table class="no-border">
    <tr>
        <td width="50%">
            <strong>To</strong><br>
            <div class="small">{{ $po->supplier->name }}</div>
            <div class="small">{{ $po->supplier->address }}</div>
        </td>
        <td width="50%" class="text-right">
            <strong>Contact</strong><br>
            <div class="small">Tel : {{ $po->supplier->phone ?? '-' }}</div>
            <div class="small">PIC : {{ $po->supplier->pic ?? '-' }}</div>
        </td>
    </tr>
</table> --}}

{{-- <br> --}}

{{-- ITEMS --}}
<h3>PLEASE PROVIDE</h3>
<table class="table-border">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="40%">Description</th>
            <th width="10%" class="text-center">Qty</th>
            <th width="10%" class="text-center">Unit</th>
            <th width="15%" class="text-right">Unit Price</th>
            <th width="20%" class="text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($po->items as $i => $item)
        <tr>
            <td class="text-center">{{ $i+1 }}</td>
            <td>{{ $item->product->name }}</td>
            <td class="text-center">{{ number_format($item->qty) }}</td>
            <td class="text-center">{{ $item->product->unit ?? 'LTR' }}</td>
            <td class="text-right">{{ number_format($item->unit_price,0,',','.') }}</td>
            <td class="text-right">{{ number_format($item->total_price,0,',','.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<br>

{{-- TOTAL --}}
<table class="no-border">
    <tr>
        <td width="65%" class="small">
            <strong>Note:</strong><br>
            1. Mohon cantumkan PO No di setiap invoice<br>
            2. Barang sesuai spesifikasi & SOP K3<br><br>
            <strong>Payment:</strong> {{ $po->payment_term }}
        </td>
        <td width="35%">
            <table class="no-border">
                <tr><td class="small">Subtotal</td><td class="text-right small">{{ number_format($po->subtotal,0,',','.') }}</td></tr>
                <tr><td class="small">PPN 11%</td><td class="text-right small">{{ number_format($po->tax,0,',','.') }}</td></tr>
                <tr><td class="small">Shipping</td><td class="text-right small">{{ number_format($po->shipping_cost ?? 0,0,',','.') }}</td></tr>
                <tr><td><strong>TOTAL</strong></td><td class="text-right"><strong>{{ number_format($po->grand_total + ($po->shipping_cost ?? 0),0,',','.') }}</strong></td></tr>
            </table>
        </td>
    </tr>
</table>

<br><br>

{{-- SIGN --}}
<table class="no-border text-center">
    <tr>
        <td width="33%">Seller<br><br><br><br><br>__________________</td>
        <td width="33%">Required<br><br><br><br><br>MASHITA NA<br><small>(Purchasing)</small></td>
        <td width="33%">Approved<br><br><br><br><br>RIZAL GHOZALI<br><small>(Branch Manager)</small></td>
    </tr>
</table>

<footer>
    <img src="{{ public_path('footer.jpg') }}">
</footer>

</body>
</html>
