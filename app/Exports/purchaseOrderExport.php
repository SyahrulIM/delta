<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseOrderExport implements FromCollection, WithHeadings
{
    // protected $poId;

    // public function __construct($poId)
    // {
    //     $this->poId = $poId;
    // }

    public function collection()
    {
        $rows = collect();

        $po = PurchaseOrder::with([
            'project',
            'items',
            'items.product',
            'supplier',
            'invoices.poPayments'
        ])->get();

        foreach ($po as $purchaseOrder) {

            $ss = 0;

            foreach ($purchaseOrder->invoices as $key => $value) {
                foreach ($value->poPayments as $i => $pay) {
                    // print_r($value->grand_total);
                    // echo '|';
                    $ss += $pay->sub_pembayaran;
                }
            }
            // echo $ss;

            $sisa = (float)$purchaseOrder->grand_total - $ss;
            // dd($sisa);

            // dd($purchaseOrder->grand_total, $ss);

            $rows->push([
                'PO Number' => $purchaseOrder->po_number,
                'Job'         => $purchaseOrder->job,
                'PO Date'     => $purchaseOrder->po_date,
                'Supplier'    => $purchaseOrder->supplier->name,
                'Project'    => $purchaseOrder->project->name,
                'Contract'    => $purchaseOrder->contract_no,
                'Product'     => $purchaseOrder->items->pluck('product.name')->implode(', '),
                'Invoice' => '',
                'Tanggal Invoice' => '',
                'Amount' => $purchaseOrder->grand_total,
                'Sisa Bayar' => ($sisa == 0) ? 'NOL' : $sisa,
            ]);

            foreach ($purchaseOrder->invoices as $invoice) {

            // 🔷 HEADER INVOICE
            $rows->push([
                'PO Number' => '',
                'Job'         => '',
                'PO Date'     => '',
                'Supplier'    => '',
                'Project'    => '',
                'Contract'    => '',
                'Product'     => '',
                'Invoice' => $invoice->invoice_number,
                'Tanggal Invoice' => $invoice->invoice_date,
                'Amount' => $invoice->grand_total,
                'Sisa Bayar' => '',
            ]);

            // 🔸 DETAIL PEMBAYARAN
            foreach ($invoice->poPayments as $i => $pay) {
                $rows->push([
                    'PO Number' => '',
                    'Job'         => '',
                    'PO Date'     => '',
                    'Supplier'    => '',
                    'Project'    => '',
                    'Contract'    => '',
                    'Product'     => '',
                    'Invoice' => '   Pembayaran ' . ($i + 1),
                    'Tanggal Invoice' => $pay->sub_tanggal,
                    'Amount' => $pay->sub_pembayaran,
                    'Sisa Bayar' => '',
                ]);
            }

            // spacer
            // $rows->push(['', '', '', '', '']);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'PO Number',
            'Job',
            'PO Date' ,
            'Supplier',
            'Project',
            'Contract',
            'Product' ,
            'Invoice',
            'Tanggal Invoice',
            'Amount',
            'Sisa Bayar',
        ];
    }
}
