<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\SalesOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

namespace App\Exports;

use App\Models\Project;
use App\Models\SalesOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProjectExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $rows = collect();
        $salesOrders = SalesOrder::with([
            'project',
            'items.product',
            'items',
            'invoices',
            'invoices.items',
            'invoices.piutangs'
        ])->get();

        foreach ($salesOrders as $key => $so) {

            // dd($so->invoices[$key]->items->sum('qty'));

            $ss = 0;

            foreach ($so->invoices as $key => $value) {
                $ss += $value->items->sum('qty');
            }

            // $po = PurchaseOrder::where('job', $so->id)->first();

            // dd($so->items->sum('qty') - $ss);

            // 🔷 HEADER SO
            $rows->push([
                'SO Number'   => $so->so_number,
                'Job'         => $so->job,
                'SO Date'     => $so->so_date,
                'Customer'    => $so->customer_name,
                'Project'    => $so->project->name,
                'Contract'    => $so->contract_no,
                'Product'     => $so->items->pluck('product.name')->implode(', '),
                'Qty'         => $so->items->sum('qty'),
                'Qty Invoice' => '',
                'Sisa Qty'    => ($so->items->sum('qty') - $ss == 0)?'NOL':($so->items->sum('qty') - $ss),
                'Invoice'     => 'TOTAL',
                'Tgl Pembayaran' => '',
                'Amount'      => $so->grand_total,
                'Sisa Invoice'        => '',
                'Sisa Total'        => '',
                'Bank'        => '',
                'Note'        => '',
            ]);

            $sisa_total = $so->grand_total;

            foreach ($so->invoices as $inv) {

                // dd($inv->items->sum('qty'));
                // $a = $inv->items[0]->qty;

                $totalInvoice = $inv->grand_total ?? 0;
                $totalBayar   = $inv->piutangs->sum('amount');
                $sisa         = $totalInvoice - $totalBayar;
                $sisa_total   -= $totalBayar;

                // 🔶 INVOICE
                $rows->push([
                    'SO Number'   => '',
                    'Job'         => '',
                    'SO Date'     => '',
                    'Customer'    => '',
                    'Project'    => '',
                    'Contract'    => '',
                    'Product'     => '',
                    'Qty'         => '',
                    'Qty Invoice' => $inv->items->sum('qty'),
                    'Sisa Qty' => '',
                    'Invoice'     => 'Invoice - ' . $inv->invoice_number,
                    'Tgl Pembayaran' => '',
                    'Amount'      => $inv->grand_total,
                    'Sisa Invoice'        => $sisa,
                    'Sisa Total'        => '',
                    'Bank'        => '',
                    'Note'        => '',
                ]);

                // 🔸 PIUTANG
                foreach ($inv->piutangs as $i => $p) {

                    $rows->push([
                        'SO Number'   => '',
                        'Job'         => '',
                        'SO Date'     => '',
                        'Customer'    => '',
                        'Project'    => '',
                        'Contract'    => '',
                        'Product'     => '',
                        'Qty'     => '',
                        'Qty Invoice'     => '',
                        'Sisa Qty'     => '',
                        'Invoice'     => '   Pembayaran ' . ($i + 1),
                        'Tgl Pembayaran' => $p->date,
                        'Amount'      => $p->amount,
                        'Sisa Invoice'        => '',
                        'Sisa Total'        => '',
                        'Bank'        => $p->bank,
                        'Note'        => $p->note,
                    ]);
                }
            }

        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'SO Number',
            'Job',
            'SO Date',
            'Customer',
            'Project',
            'Contract',
            'Product',
            'Qty',
            'Qty Invoice',
            'Sisa Qty',
            'Invoice',
            'Tgl Pembayaran',
            'Amount',
            'Sisa Invoice',
            'Sisa Total',
            'Bank',
            'Note',
        ];
    }
}
