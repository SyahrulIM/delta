<?php

namespace App\Exports;

use App\Models\SalesOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MarginExport implements FromCollection, WithHeadings
{
    protected $so;

    public function __construct($so)
    {
        $this->so = $so;
    }

    public function collection()
    {

        $rows = collect();

        $so = $this->so->load([
            'project',
            'items.product',
            'items.margin',
            'items.sub'
        ]);

        $fields = [
            'contractor_percent' => 'Fee Kontraktor',
            'expedition_percent' => 'Biaya Expedisi',
            'test_percent' => 'Biaya Tes',
            'marketing_percent' => 'Marketing',
            'trip_percent' => 'Perjalanan',
            'salary_percent' => 'Gaji',
            'pph_percent' => 'PPH',
            'scf_percent' => 'SCF',
            'bunga_bank_percent' => 'Bunga Bank',
            'retensi_percent' => 'Retensi',
        ];

        // dd($fields);

        foreach ($so->items as $item) {

            $cost = $item->margin;

            // 🔷 Header Item
            $rows->push([
                'SO' => $so->so_number,
                'Job'         => $so->job,
                'SO Date'     => $so->so_date,
                'Customer'    => $so->customer_name,
                'Project'    => $so->project->name,
                'Contract'    => $so->contract_no,
                'Item' => $item->product->name,
                'Field' => 'TOTAL',
                'Percent' => $cost->total_percent ?? 0,
                'Nominal' => $cost->total_netto ?? 0,
                'Tanggal Sub' => '',
                'Sub Pembayaran' => '',
                'Sisa Pembayaran' => '',
                'No Invoice' => '',
            ]);

            foreach ($fields as $field => $label) {

                $percent = $cost->$field ?? 0;
                $nominal = ($percent / 100) * $item->total_price;

                // 🔸 Sub pembayaran
                $subs = $item->sub->where('field', $field);

                // $ss = 0;
                // foreach ($subs as $sub) {
                //     $ss += $sub->sub_pembayaran;
                // }

                // total sub pembayaran
                $totalSub = $subs->sum('sub_pembayaran');

                // hitung sisa
                $sisa = $nominal - $totalSub;

                // 🔶 Baris utama biaya
                $rows->push([
                    'SO' => '',
                    'Job'         => '',
                    'SO Date'     => '',
                    'Customer'    => '',
                    'Project'    => '',
                    'Contract'    => '',
                    'Item' => '',
                    'Field' => $label,
                    'Percent' => $percent,
                    'Nominal' => $nominal,
                    'Tanggal Sub' => '',
                    'Sub Pembayaran' => '',
                    'Sisa Pembayaran' => $sisa,
                    'No Invoice' => '',
                ]);


                foreach ($subs as $sub) {
                    $rows->push([
                        'SO' => '',
                        'Job'         => '',
                        'SO Date'     => '',
                        'Customer'    => '',
                        'Project'    => '',
                        'Contract'    => '',
                        'Item' => '',
                        'Field' => '   Sub',
                        'Percent' => '',
                        'Nominal' => '',
                        'Tanggal Sub' => $sub->sub_tanggal,
                        'Sub Pembayaran' => $sub->sub_pembayaran,
                        'Sisa Pembayaran' => '',
                        'No Invoice' => $sub->sub_invoice,
                    ]);
                }
            }

            // spacer
            $rows->push(['', '', '', '', '', '', '']);
        }

        // dd($rows);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'SO',
            'Job',
            'SO Date',
            'Customer',
            'Project',
            'Contract',
            'Item',
            'Field',
            'Percent (%)',
            'Nominal',
            'Tanggal Sub',
            'Sub Pembayaran',
            'Sisa Pembayaran',
            'No Invoice',
        ];
    }
}
