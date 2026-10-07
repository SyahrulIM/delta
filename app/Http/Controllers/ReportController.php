<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function reportData(): array
    {

        // PurchaseOrder::

        return [
            'labels' => ['JAN I', 'JAN II', 'FEB I', 'FEB II', 'MAR I', 'MAR II'],

            // Rekapitulasi – nilai per periode
            'rekapData' => [
                'kontrak'     => [1940399111, 1538637969, 307249887,  1738662930, 1018414067, 0],
                'payment'     => [1321247708, 2382565963, 772166232,  1304439098, 1897183574, 0],
                'po_supplier' => [1866019958, 727895537,  1051212306, 689864288,  981491068,  0],
                'invoice'     => [1957617003, 0,           1504249800, 541484141,  1065603830, 0],
            ],

            // Akumulasi – nilai kumulatif
            'akumulasiData' => [
                'kontrak'     => [1748107307, 2938420557, 3215222257, 4781585257, 5699075407, 5699075407],
                'payment'     => [1538637969, 3921203932, 4693370164, 5997809261, 7894992835, 7894992835],
                'po_supplier' => [1866019958, 2593915495, 3645127801, 4334992089, 5316483157, 5316483157],
                'invoice'     => [1957617003, 1957617003, 3461866803, 4003350944, 5068954774, 5068954774],
            ],
        ];
    }

    // ── Web view ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $data = $this->reportData();
        $year      = $request->integer('year',       now()->year);
        $monthFrom = $request->integer('month_from', 1);
        $monthTo   = $request->integer('month_to',   now()->month);

        // Validasi urutan
        if ($monthFrom > $monthTo) $monthFrom = $monthTo;

        $labels        = $this->buildLabels($year, $monthFrom, $monthTo);
        $rekapData     = $this->getRekapData($year, $monthFrom, $monthTo);
        // $akumulasiData = $rekapData;
        $akumulasiData = $this->buildAkumulasi($rekapData);

        $monthNames = $this->getMonthNames($monthFrom, $monthTo, $year);

        return view('report.index', compact(
            'labels', 'rekapData', 'akumulasiData',
            'year', 'monthFrom', 'monthTo', 'monthNames', 'data'
        ));
    }

    // ── PDF export ─────────────────────────────────────────────────────────────
    // Menerima base64 screenshot chart dari browser (via JS fetch),
    // lalu merender Blade PDF dan mengirimkannya sebagai file unduhan.
    public function exportPdf2(Request $request)
    {
        $request->validate([
            'chart_akumulasi' => 'required|string',
            'chart_rekap'     => 'required|string',
        ]);

        $data = $this->reportData();
        $data['chartAkumulasiImg'] = $request->input('chart_akumulasi');
        $data['chartRekapImg']     = $request->input('chart_rekap');
        $data['exportedAt']        = now()->translatedFormat('d F Y, H:i') . ' WIB';

        $pdf = Pdf::loadView('trading.report-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'sans-serif',
                'dpi'                  => 150,
            ]);

        $filename = 'Laporan_Trading_DSI_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'chart_akumulasi' => 'required|string',
            'chart_rekap'     => 'required|string',
            'year'            => 'required|integer',
            'month_from'      => 'required|integer|min:1|max:12',
            'month_to'        => 'required|integer|min:1|max:12',
        ]);

        $year      = $request->integer('year');
        $monthFrom = $request->integer('month_from');
        $monthTo   = $request->integer('month_to');

        $labels        = $this->buildLabels($year, $monthFrom, $monthTo);
        $rekapData     = $this->getRekapData($year, $monthFrom, $monthTo);
        // $akumulasiData = $rekapData;
        $akumulasiData = $this->buildAkumulasi($rekapData);
        $monthNames    = $this->getMonthNames($monthFrom, $monthTo, $year);

        $data = [
            'labels'           => $labels,
            'rekapData'        => $rekapData,
            'akumulasiData'    => $akumulasiData,
            'monthNames'       => $monthNames,
            'year'             => $year,
            'chartAkumulasiImg'=> $request->input('chart_akumulasi'),
            'chartRekapImg'    => $request->input('chart_rekap'),
            'exportedAt'       => now()->translatedFormat('d F Y, H:i') . ' WIB',
        ];
// dd($data);
//         try {
//             $pdf = Pdf::loadView('report.report-pdf', $data)
//             ->setPaper('a4', 'landscape')
//             ->setOptions([
//                 'isHtml5ParserEnabled' => true,
//                 'isRemoteEnabled'      => false,
//                 'defaultFont'          => 'sans-serif',
//                 'dpi'                  => 150,
//             ]);

//         $filename = 'Laporan_Trading_DSI_' . now()->format('Ymd_His') . '.pdf';

//         } catch (\Exception $e) {
//             return response()->json([
//                 'error'   => $e->getMessage(),
//                 'file'    => $e->getFile(),
//                 'line'    => $e->getLine(),
//             ], 500);
//         }

        $pdf = Pdf::loadView('report.report-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'sans-serif',
                'dpi'                  => 150,
            ]);

        $filename = 'Laporan_Trading_DSI_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($filename);
        // return $pdf->stream($filename);
    }

    private function buildLabels(int $year, int $monthFrom, int $monthTo): array
    {
        $labels    = [];
        $monthAbbr = ['','JAN','FEB','MAR','APR','MEI','JUN','JUL','AGS','SEP','OKT','NOV','DES'];

        for ($m = $monthFrom; $m <= $monthTo; $m++) {
            $lastDay = Carbon::create($year, $m)->daysInMonth;
            $labels[] = $monthAbbr[$m] . ' I';   // tgl 1–15
            $labels[] = $monthAbbr[$m] . ' II';  // tgl 16–akhir bulan
        }

        return $labels;
    }

    /**
     * Ambil total PO per periode (setengah bulan) dari DB.
     * Hasil: ['po_supplier' => [val1, val2, ...], 'kontrak' => [...], ...]
     */
    private function getRekapData2(int $year, int $monthFrom, int $monthTo): array
    {
        $fromDate = Carbon::create($year, $monthFrom, 1)->startOfDay();
        $toDate   = Carbon::create($year, $monthTo)->endOfMonth()->endOfDay();

        // po
        $rows = DB::table('purchase_orders')
            ->join('purchase_order_items',
                   'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->selectRaw("
                MONTH(purchase_orders.po_date)                 AS bulan,
                IF(DAY(purchase_orders.po_date) <= 15, 1, 2)  AS half,
                '_all' AS status,
                SUM(purchase_order_items.total_price)             AS total
            ")
            ->whereBetween('purchase_orders.po_date', [$fromDate, $toDate])
            ->groupByRaw('bulan, half, status')
            ->get();

        // Index: "3-1" => ['approved' => 1200000, 'paid' => ...]
        $indexed = [];
        foreach ($rows as $row) {
            $key = $row->bulan . '-' . $row->half;
            $indexed[$key][$row->status] = (int) $row->total;

            // Akumulasi semua status untuk po_supplier
            $indexed[$key]['_all'] = ($indexed[$key]['_all'] ?? 0) + (int) $row->total;
        }

        // pembayaran
        $rows = DB::table('purchase_order_payments')
            // ->join('purchase_order_items',
            //        'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->selectRaw("
                MONTH(purchase_order_payments.sub_tanggal)                 AS bulan,
                IF(DAY(purchase_order_payments.sub_tanggal) <= 15, 1, 2)  AS half,
                'paid' AS status,
                SUM(purchase_order_payments.sub_pembayaran)             AS total
            ")
            ->whereBetween('purchase_order_payments.sub_tanggal', [$fromDate, $toDate])
            ->groupByRaw('bulan, half, status')
            ->get();

        // Index: "3-1" => ['approved' => 1200000, 'paid' => ...]
        $indexed = [];
        foreach ($rows as $row) {
            $key = $row->bulan . '-' . $row->half;
            $indexed[$key][$row->status] = (int) $row->total;

            // Akumulasi semua status untuk po_supplier
            $indexed[$key]['_all'] = ($indexed[$key]['_all'] ?? 0) + (int) $row->total;
        }

        $result = ['kontrak' => [], 'payment' => [], 'po_supplier' => [], 'invoice' => []];

        for ($m = $monthFrom; $m <= $monthTo; $m++) {
            foreach ([1, 2] as $half) {
                $key = "$m-$half";
                // Sesuaikan nilai status dengan data di tabel kamu
                $result['kontrak'][]     = $indexed[$key]['approved']  ?? 0;
                $result['payment'][]     = $indexed[$key]['paid']       ?? 0;
                $result['po_supplier'][] = $indexed[$key]['_all']       ?? 0;
                $result['invoice'][]     = $indexed[$key]['invoiced']   ?? 0;
            }
        }

        return $result;
    }

    private function getRekapData(int $year, int $monthFrom, int $monthTo): array
    {
        $from = Carbon::create($year, $monthFrom, 1)->startOfDay();
        $to   = Carbon::create($year, $monthTo)->endOfMonth()->endOfDay();

        // ── 1. PO Supplier ────────────────────────────────────────────────
        $poRows = DB::table('purchase_order_payments')
            // ->join('purchase_order_payments',
            //     'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->selectRaw("
                MONTH(sub_tanggal)                AS bulan,
                IF(DAY(sub_tanggal) <= 15, 1, 2) AS half,
                SUM(purchase_order_payments.sub_pembayaran)            AS total
            ")
            ->whereBetween('sub_tanggal', [$from, $to])
            ->groupByRaw('bulan, half')
            ->get()
            ->keyBy(fn($r) => "{$r->bulan}-{$r->half}");

        // ── 2. Payment ────────────────────────────────────────────────────
        $paymentRows = DB::table('piutangs')
            ->selectRaw("
                MONTH(date)                AS bulan,
                IF(DAY(date) <= 15, 1, 2) AS half,
                SUM(amount)               AS total
            ")
            ->whereBetween('date', [$from, $to])
            ->groupByRaw('bulan, half')
            ->get()
            ->keyBy(fn($r) => "{$r->bulan}-{$r->half}");

        // ── 3. Invoice ────────────────────────────────────────────────────
        $invoiceRows = DB::table('invoices')
            ->selectRaw("
                MONTH(invoice_date)                AS bulan,
                IF(DAY(invoice_date) <= 15, 1, 2) AS half,
                SUM(grand_total)                AS total
            ")
            ->where('type', 'SalesOrder')
            ->whereBetween('invoice_date', [$from, $to])
            ->groupByRaw('bulan, half')
            ->get()
            ->keyBy(fn($r) => "{$r->bulan}-{$r->half}");

        // ── 4. Kontrak ────────────────────────────────────────────────────
        $kontrakRows = DB::table('sales_orders')
            ->selectRaw("
                MONTH(so_date)                AS bulan,
                IF(DAY(so_date) <= 15, 1, 2) AS half,
                SUM(grand_total)                AS total
            ")
            ->whereBetween('so_date', [$from, $to])
            ->groupByRaw('bulan, half')
            ->get()
            ->keyBy(fn($r) => "{$r->bulan}-{$r->half}");

        // ── Susun hasil per label periode ─────────────────────────────────
        $result = ['po_supplier' => [], 'payment' => [], 'invoice' => [], 'kontrak' => []];

        for ($m = $monthFrom; $m <= $monthTo; $m++) {
            foreach ([1, 2] as $half) {
                $key = "$m-$half";
                $result['po_supplier'][] = (int) ($poRows[$key]->total      ?? 0);
                $result['payment'][]     = (int) ($paymentRows[$key]->total  ?? 0);
                $result['invoice'][]     = (int) ($invoiceRows[$key]->total  ?? 0);
                $result['kontrak'][]     = (int) ($kontrakRows[$key]->total  ?? 0);
            }
        }

        // dd($result);

        return $result;
    }

    /**
     * Hitung nilai kumulatif dari data rekapitulasi.
     */
    private function buildAkumulasi(array $rekap): array
    {
        $akumulasi = [];
        foreach ($rekap as $key => $values) {
            $cum = 0;
            $akumulasi[$key] = array_map(function ($v) use (&$cum) {
                return $cum += $v;
            }, $values);
        }

        // dd($akumulasi);
        return $akumulasi;
    }

    /**
     * Label teks untuk judul periode, misal "Januari – Maret 2026"
     */
    private function getMonthNames(int $monthFrom, int $monthTo, int $year): string
    {
        $locale = ['','Januari','Februari','Maret','April','Mei','Juni',
                   'Juli','Agustus','September','Oktober','November','Desember'];

        if ($monthFrom === $monthTo) {
            return $locale[$monthFrom] . ' ' . $year;
        }
        return $locale[$monthFrom] . ' – ' . $locale[$monthTo] . ' ' . $year;
    }
}
