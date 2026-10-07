<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProjectExport implements FromArray, WithEvents
{
    protected array $data        = [];
    protected array $mergeCells  = [];
    protected int   $currentRow  = 3; // baris mulai data (1&2 = header)

    private array $fields = [
        'contractor_percent' => 'Fee Kontraktor',
        'expedition_percent' => 'Biaya Expedisi',
        'test_percent'       => 'Biaya Tes',
        'marketing_percent'  => 'Marketing',
        'trip_percent'       => 'Perjalanan',
        'salary_percent'     => 'Gaji',
        'pph_percent'        => 'PPH',
        'scf_percent'        => 'SCF',
        'bunga_bank_percent' => 'Bunga Bank',
        'retensi_percent'    => 'Retensi',
    ];

    // Struktur kolom tetap — posisi ditentukan saat build()
    protected array $colMap = [];

    public function array(): array
    {
        $this->build();
        return $this->data;
    }

    private function build(): void
    {
        $salesOrders = SalesOrder::with([
            'project',
            'items.product',
            'items.margin',
            'items.sub',
            'invoices.items',
            'invoices.piutangs',
        ])->get();

        $purchaseOrders = PurchaseOrder::with([
            'project',
            'items.product',
            'supplier',
            'invoices.poPayments',
        ])->get();

        $poByJob = $purchaseOrders->keyBy('job');

        // ── hitung max kolom dinamis ──────────────────────────────────
        $maxInvoices   = 0;
        $maxPiutangs   = 0;
        $maxSubPerField = array_fill_keys(array_keys($this->fields), 0);
        $maxPoInvoices = 0;
        $maxPoPayments = 0;

        foreach ($salesOrders as $so) {
            $maxInvoices = max($maxInvoices, $so->invoices->count());
            foreach ($so->invoices as $inv) {
                $maxPiutangs = max($maxPiutangs, $inv->piutangs->count());
            }
            foreach ($so->items as $item) {
                foreach (array_keys($this->fields) as $field) {
                    $maxSubPerField[$field] = max(
                        $maxSubPerField[$field],
                        $item->sub->where('field', $field)->count()
                    );
                }
            }
        }
        foreach ($purchaseOrders as $po) {
            $maxPoInvoices = max($maxPoInvoices, $po->invoices->count());
            foreach ($po->invoices as $inv) {
                $maxPoPayments = max($maxPoPayments, $inv->poPayments->count());
            }
        }

        // ── bangun colMap (nama kolom → index 0-based) ────────────────
        $cols = [];

        // SO base
        foreach (['SO Number','Job','SO Date','Customer','Project','Contract','Product','Qty','Sisa Qty'] as $f) {
            $cols[$f] = count($cols);
        }

        // Invoice cols (1 group per invoice)
        for ($i = 1; $i <= $maxInvoices; $i++) {
            foreach (['No Invoice','Qty Invoice','Amount Invoice','Sisa Invoice'] as $f) {
                $cols["inv{$i}_{$f}"] = count($cols);
            }
            // Piutang sub-rows (kolom piutang tetap ada, diisi per sub-row)
            foreach (['Tgl Pembayaran','Amount Bayar','Bank','Note'] as $f) {
                $cols["inv{$i}_piutang_{$f}"] = count($cols);
            }
        }

        // Margin cols
        foreach ($this->fields as $field => $label) {
            $cols["margin_{$field}_pct"]   = count($cols);
            $cols["margin_{$field}_nom"]   = count($cols);
            $cols["margin_{$field}_sisa"]  = count($cols);
            // Sub margin
            foreach (['Tgl Sub','Sub Pembayaran','No Invoice Sub'] as $f) {
                $cols["margin_{$field}_sub_{$f}"] = count($cols);
            }
        }

        // PO base
        // foreach (['PO Number','PO Job','PO Date','Supplier','PO Project','PO Contract','PO Product','PO Total','PO Sisa'] as $f) {
        foreach (['PO Number','PO Date','Supplier','PO Total'] as $f) {
            $cols[$f] = count($cols);
        }

        // PO Invoice cols
        for ($i = 1; $i <= $maxPoInvoices; $i++) {
            foreach (['No PO Invoice','Tgl PO Invoice','Amount PO Invoice'] as $f) {
                $cols["poinv{$i}_{$f}"] = count($cols);
            }
            foreach (['Tgl PO Bayar','Amount PO Bayar'] as $f) {
                $cols["poinv{$i}_pay_{$f}"] = count($cols);
            }
        }

        foreach (['PO Sisa'] as $f) {
            $cols[$f] = count($cols);
        }

        $this->colMap  = $cols;
        $totalCols     = count($cols);

        // ── header row 1: group label ─────────────────────────────────
        $h1 = array_fill(0, $totalCols, '');
        $h2 = array_fill(0, $totalCols, '');

        $h1[$cols['SO Number']]  = 'SALES ORDER';
        $h2[$cols['SO Number']]  = 'SO Number';
        $h2[$cols['Job']]        = 'Job';
        $h2[$cols['SO Date']]    = 'SO Date';
        $h2[$cols['Customer']]   = 'Customer';
        $h2[$cols['Project']]    = 'Project';
        $h2[$cols['Contract']]   = 'Contract';
        $h2[$cols['Product']]    = 'Product';
        $h2[$cols['Qty']]        = 'Qty';
        $h2[$cols['Sisa Qty']]   = 'Sisa Qty';

        for ($i = 1; $i <= $maxInvoices; $i++) {
            $h1[$cols["inv{$i}_No Invoice"]]          = "Invoice $i";
            $h2[$cols["inv{$i}_No Invoice"]]          = 'No Invoice';
            $h2[$cols["inv{$i}_Qty Invoice"]]         = 'Qty Invoice';
            $h2[$cols["inv{$i}_Amount Invoice"]]      = 'Amount';
            $h2[$cols["inv{$i}_Sisa Invoice"]]        = 'Sisa Invoice';
            $h1[$cols["inv{$i}_piutang_Tgl Pembayaran"]] = "Invoice $i - Pembayaran";
            $h2[$cols["inv{$i}_piutang_Tgl Pembayaran"]] = 'Tgl';
            $h2[$cols["inv{$i}_piutang_Amount Bayar"]]   = 'Amount';
            $h2[$cols["inv{$i}_piutang_Bank"]]           = 'Bank';
            $h2[$cols["inv{$i}_piutang_Note"]]           = 'Note';
        }

        foreach ($this->fields as $field => $label) {
            $h1[$cols["margin_{$field}_pct"]] = "Margin: $label";
            $h2[$cols["margin_{$field}_pct"]] = '%';
            $h2[$cols["margin_{$field}_nom"]] = 'Nominal';
            $h2[$cols["margin_{$field}_sisa"]]= 'Sisa';
            $h1[$cols["margin_{$field}_sub_Tgl Sub"]]         = "Sub: $label";
            $h2[$cols["margin_{$field}_sub_Tgl Sub"]]         = 'Tgl Sub';
            $h2[$cols["margin_{$field}_sub_Sub Pembayaran"]]  = 'Sub Bayar';
            $h2[$cols["margin_{$field}_sub_No Invoice Sub"]]  = 'No Inv';
        }

        $h1[$cols['PO Number']] = 'PURCHASE ORDER';
        $h2[$cols['PO Number']] = 'PO Number';
        // $h2[$cols['PO Job']]    = 'Job';
        $h2[$cols['PO Date']]   = 'PO Date';
        $h2[$cols['Supplier']]  = 'Supplier';
        // $h2[$cols['PO Project']]= 'Project';
        // $h2[$cols['PO Contract']]='Contract';
        // $h2[$cols['PO Product']]= 'Product';
        $h2[$cols['PO Total']]  = 'Grand Total';

        for ($i = 1; $i <= $maxPoInvoices; $i++) {
            $h1[$cols["poinv{$i}_No PO Invoice"]]    = "PO Invoice $i";
            $h2[$cols["poinv{$i}_No PO Invoice"]]    = 'No Invoice';
            $h2[$cols["poinv{$i}_Tgl PO Invoice"]]   = 'Tgl Invoice';
            $h2[$cols["poinv{$i}_Amount PO Invoice"]]= 'Amount';
            $h1[$cols["poinv{$i}_pay_Tgl PO Bayar"]]     = "PO Inv $i - Bayar";
            $h2[$cols["poinv{$i}_pay_Tgl PO Bayar"]]     = 'Tgl';
            $h2[$cols["poinv{$i}_pay_Amount PO Bayar"]]  = 'Amount';
        }

        $h2[$cols['PO Sisa']]   = 'Sisa Bayar';

        $this->data[] = $h1;
        $this->data[] = $h2;

        // ── data rows ─────────────────────────────────────────────────
        foreach ($salesOrders as $so) {
            $qtyTotal    = $so->items->sum('qty');
            $qtyInvoiced = $so->invoices->sum(fn($inv) => $inv->items->sum('qty'));
            $sisaQty     = $qtyTotal - $qtyInvoiced;

            $item = $so->items->first();
            $cost = $item?->margin;

            $po       = $poByJob[$so->job] ?? null;

            $ss = 0;
            // dd($po->invoices);
            if (isset($po->invoices)) {
                foreach ($po->invoices as $key => $value) {
                    foreach ($value->poPayments as $i => $pay) {
                        // print_r($value->grand_total);
                        // echo '|';
                        $ss += $pay->sub_pembayaran;
                    }
                }
            }

            $poSisaBayar = $po
                ? ((float)$po->grand_total - $ss)
                : null;

            // hitung berapa sub-rows yang dibutuhkan SO ini
            $subRowsInv = 0;
            foreach ($so->invoices as $inv) {
                $subRowsInv = max($subRowsInv, $inv->piutangs->count());
            }

            $subRowsMargin = 0;
            if ($item) {
                foreach (array_keys($this->fields) as $field) {
                    $subRowsMargin = max($subRowsMargin, $item->sub->where('field', $field)->count());
                }
            }

            $subRowsPo = 0;
            if ($po) {
                foreach ($po->invoices as $inv) {
                    $subRowsPo = max($subRowsPo, $inv->poPayments->count());
                }
            }

            // total sub-rows (minimum 1 = baris utama)
            $totalSubRows = max(1, $subRowsInv, $subRowsMargin, $subRowsPo);

            // siapkan semua baris kosong
            $rows = [];
            for ($r = 0; $r < $totalSubRows; $r++) {
                $rows[$r] = array_fill(0, $totalCols, '');
            }

            // ── SO base (baris pertama, merge ke bawah) ──────────────
            $rows[0][$cols['SO Number']] = $so->so_number;
            $rows[0][$cols['Job']]       = $so->job;
            $rows[0][$cols['SO Date']]   = $so->so_date;
            $rows[0][$cols['Customer']]  = $so->customer_name;
            $rows[0][$cols['Project']]   = $so->project->name;
            $rows[0][$cols['Contract']]  = $so->contract_no;
            $rows[0][$cols['Product']]   = $so->items->pluck('product.name')->implode(', ');
            $rows[0][$cols['Qty']]       = $qtyTotal;
            $rows[0][$cols['Sisa Qty']]  = $sisaQty == 0 ? 'NOL' : $sisaQty;

            // ── Invoice header (baris pertama) ───────────────────────
            foreach ($so->invoices as $idx => $inv) {
                $i = $idx + 1;
                if ($i > $maxInvoices) break;
                $rows[0][$cols["inv{$i}_No Invoice"]]     = $inv->invoice_number;
                $rows[0][$cols["inv{$i}_Qty Invoice"]]    = $inv->items->sum('qty');
                $rows[0][$cols["inv{$i}_Amount Invoice"]] = $inv->grand_total;
                $rows[0][$cols["inv{$i}_Sisa Invoice"]]   = $inv->grand_total - $inv->piutangs->sum('amount');

                // Piutang sub-rows
                foreach ($inv->piutangs as $pIdx => $p) {
                    $rows[$pIdx][$cols["inv{$i}_piutang_Tgl Pembayaran"]] = $p->date;
                    $rows[$pIdx][$cols["inv{$i}_piutang_Amount Bayar"]]   = $p->amount;
                    $rows[$pIdx][$cols["inv{$i}_piutang_Bank"]]           = $p->bank;
                    $rows[$pIdx][$cols["inv{$i}_piutang_Note"]]           = $p->note;
                }
            }

            // ── Margin ───────────────────────────────────────────────
            foreach ($this->fields as $field => $label) {
                $percent  = $cost?->$field ?? 0;
                $nominal  = $item ? ($percent / 100) * $item->total_price : 0;
                $subs     = $item ? $item->sub->where('field', $field)->values() : collect();
                $totalSub = $subs->sum('sub_pembayaran');
                $sisa     = $nominal - $totalSub;

                $rows[0][$cols["margin_{$field}_pct"]]  = $percent;
                $rows[0][$cols["margin_{$field}_nom"]]  = $nominal;
                $rows[0][$cols["margin_{$field}_sisa"]] = $sisa;

                foreach ($subs as $sIdx => $sub) {
                    $rows[$sIdx][$cols["margin_{$field}_sub_Tgl Sub"]]        = $sub->sub_tanggal;
                    $rows[$sIdx][$cols["margin_{$field}_sub_Sub Pembayaran"]] = $sub->sub_pembayaran;
                    $rows[$sIdx][$cols["margin_{$field}_sub_No Invoice Sub"]] = $sub->sub_invoice;
                }
            }

            // ── PO ───────────────────────────────────────────────────
            if ($po) {
                $rows[0][$cols['PO Number']]  = $po->po_number;
                // $rows[0][$cols['PO Job']]     = $po->job;
                $rows[0][$cols['PO Date']]    = $po->po_date;
                $rows[0][$cols['Supplier']]   = $po->supplier->name;
                // $rows[0][$cols['PO Project']] = $po->project->name;
                // $rows[0][$cols['PO Contract']]= $po->contract_no;
                // $rows[0][$cols['PO Product']] = $po->items->pluck('product.name')->implode(', ');
                $rows[0][$cols['PO Total']]   = $po->grand_total;

                foreach ($po->invoices as $idx => $inv) {
                    $i = $idx + 1;
                    if ($i > $maxPoInvoices) break;
                    $rows[0][$cols["poinv{$i}_No PO Invoice"]]    = $inv->invoice_number;
                    $rows[0][$cols["poinv{$i}_Tgl PO Invoice"]]   = $inv->invoice_date;
                    $rows[0][$cols["poinv{$i}_Amount PO Invoice"]]= $inv->grand_total;

                    foreach ($inv->poPayments as $pIdx => $pay) {
                        $rows[$pIdx][$cols["poinv{$i}_pay_Tgl PO Bayar"]]    = $pay->sub_tanggal;
                        $rows[$pIdx][$cols["poinv{$i}_pay_Amount PO Bayar"]] = $pay->sub_pembayaran;
                    }
                }
                $rows[0][$cols['PO Sisa']]    = $poSisaBayar == 0 ? 'NOL' : $poSisaBayar;
            }

            // ── simpan rows + catat merge ─────────────────────────────
            $startRow = $this->currentRow;
            foreach ($rows as $row) {
                $this->data[] = $row;
                $this->currentRow++;
            }

            // merge SO base cols jika ada sub-rows
            if ($totalSubRows > 1) {
                $endRow = $this->currentRow - 1;
                $soMergeCols = ['SO Number','Job','SO Date','Customer','Project','Contract','Product','Qty','Sisa Qty'];
                foreach ($soMergeCols as $colName) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols[$colName] + 1);
                    $this->mergeCells[] = "{$colLetter}{$startRow}:{$colLetter}{$endRow}";
                }

                // merge invoice header cols
                foreach ($so->invoices as $idx => $inv) {
                    $i = $idx + 1;
                    if ($i > $maxInvoices) break;
                    foreach (['No Invoice','Qty Invoice','Amount Invoice','Sisa Invoice'] as $f) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols["inv{$i}_{$f}"] + 1);
                        $this->mergeCells[] = "{$colLetter}{$startRow}:{$colLetter}{$endRow}";
                    }
                }

                // merge margin header cols
                foreach (array_keys($this->fields) as $field) {
                    foreach (['pct','nom','sisa'] as $suffix) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols["margin_{$field}_{$suffix}"] + 1);
                        $this->mergeCells[] = "{$colLetter}{$startRow}:{$colLetter}{$endRow}";
                    }
                }

                // merge PO base cols
                if ($po) {
                    // $poMergeCols = ['PO Number','PO Job','PO Date','Supplier','PO Project','PO Contract','PO Product','PO Total','PO Sisa'];
                    $poMergeCols = ['PO Number','PO Date','Supplier','PO Total'];
                    foreach ($poMergeCols as $colName) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols[$colName] + 1);
                        $this->mergeCells[] = "{$colLetter}{$startRow}:{$colLetter}{$endRow}";
                    }

                    foreach ($po->invoices as $idx => $inv) {
                        $i = $idx + 1;
                        if ($i > $maxPoInvoices) break;
                        foreach (['No PO Invoice','Tgl PO Invoice','Amount PO Invoice'] as $f) {
                            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols["poinv{$i}_{$f}"] + 1);
                            $this->mergeCells[] = "{$colLetter}{$startRow}:{$colLetter}{$endRow}";
                        }
                    }

                    $poMergeCols = ['PO Sisa'];
                    foreach ($poMergeCols as $colName) {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols[$colName] + 1);
                        $this->mergeCells[] = "{$colLetter}{$startRow}:{$colLetter}{$endRow}";
                    }
                }
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $totalCols = count($this->colMap);
                $lastCol   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

                // merge cells
                foreach ($this->mergeCells as $range) {
                    $sheet->mergeCells($range);
                    $sheet->getStyle($range)->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                // header row 1 style
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5496']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // header row 2 style
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'BDD7EE']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // freeze header
                $sheet->freezePane('A3');

                // auto width
                for ($col = 1; $col <= $totalCols; $col++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
                }

                // border semua data
                $lastRow = $this->currentRow - 1;
                if ($lastRow >= 3) {
                    $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color'       => ['rgb' => 'CCCCCC'],
                            ],
                        ],
                    ]);
                }
            },
        ];
    }
}
