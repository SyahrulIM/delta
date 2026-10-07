<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SoInvoiceExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return ['SO', 'Keterangan', 'Nominal', 'Sisa'];
    }

    public function collection()
    {
        $rows = collect();

        $salesOrders = SalesOrder::with(['items.sub'])->get();

        foreach ($salesOrders as $so) {

            // HEADER SO
            $rows->push([
                'SO' => $so->code,
                'Keterangan' => 'TOTAL',
                'Nominal' => $so->total_price,
                'Sisa' => '',
            ]);

            foreach ($so->items as $item) {

                $totalInvoice = $item->total_price;

                // ambil semua sub (piutang)
                $subs = $item->sub;

                $totalBayar = $subs->sum('sub_pembayaran');
                $sisa = $totalInvoice - $totalBayar;

                // INVOICE
                $rows->push([
                    'SO' => '',
                    'Keterangan' => 'Invoice - ' . $item->product->name,
                    'Nominal' => $totalInvoice,
                    'Sisa' => $sisa,
                ]);

                // PIUTANG
                foreach ($subs as $i => $sub) {

                    $rows->push([
                        'SO' => '',
                        'Keterangan' => '   Piutang ' . ($i + 1),
                        'Nominal' => $sub->sub_pembayaran,
                        'Sisa' => '',
                    ]);
                }
            }

            // spacing antar SO
            $rows->push(['', '', '', '']);
        }

        return $rows;
    }
}
