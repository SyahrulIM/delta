<?php

namespace App\Http\Controllers;

use App\Exports\MarginExport;
use App\Exports\SalesOrderExport;
use App\Models\OrderCost;
use App\Models\PembayaranSalesOrder;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesOrderSub;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class SalesOrderController extends Controller
{
     public function index()
    {
        return view('sales_orders.index');
    }

    // datatable ajax
    public function data()
    {
        $sos = SalesOrder::with('customer', 'project')
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'data' => $sos
        ]);
    }

    public function create()
    {
        $products = Product::all();
        return view('sales_orders.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'so_number' => 'required|unique:sales_orders,so_number',
            'so_date'   => 'required|date',
            'project_id'  => 'required',
            'job'           => 'required|string|max:255',
            // 'invoice_no'      => 'required|string|max:255',
            'pr_no'         => 'nullable',
            'contract_no'   => 'required|string|max:255',

            // 'spm_number' => 'nullable|string|max:255',
            'project_name' => 'nullable',
            'customer_id'   => 'nullable',
            // 'customer_name' => 'nullable',
            // 'customer_address' => 'nullable',
            // 'contact_person' => 'nullable',
            // 'phone' => 'nullable',

            'items.*.product_id' => 'required',
            'items.*.qty'        => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $subtotal = collect($request->items)
            ->sum(fn($i) => $i['qty'] * $i['unit_price']);

        $tax = $subtotal * 0.11;
        $grand = $subtotal + $tax;

        $so = SalesOrder::create([
            'so_number' => $request->so_number,
            'so_date'   => $request->so_date,
            'job'         => $request->job,
            // 'invoice_no'    => $request->invoice_no,
            'contract_no'    => $request->contract_no,
            // 'pr_no'       => $request->pr_no,

            // 'spm_number' => $request->spm_number,
            'project_id' => $request->project_id,
            // 'project_id' => $request->project_id,
            'customer_name' => $request->customer_name,
            // 'customer_address' => $request->customer_address,
            // 'contact_person' => $request->contact_person,
            // 'phone' => $request->phone,

            'subtotal' => $subtotal,
            'tax' => $tax,
            'grand_total' => $grand,
            // 'notes' => $request->notes,
        ]);

        foreach ($request->items as $item) {
            SalesOrderItem::create([
                'sales_order_id' => $so->id,
                'product_id'        => $item['product_id'],
                'qty'               => $item['qty'],
                'unit_price'        => $item['unit_price'],
                'total_price'       => $item['qty'] * $item['unit_price'],
            ]);
        }

        return response()->json([
            'success' => true,
            'redirect' => route('sales_orders.index')
        ]);
    }

    public function edit(SalesOrder $so)
    {
        $products = Product::all();
        $so->load('items.product');

        return view('sales_orders.edit', [
            'so' => $so,
            'products' => $products
        ]);
    }

    public function update(Request $request, SalesOrder $so)
    {
        $validated = $request->validate([
            'so_date'       => 'required',
            'project_id'  => 'required',
            'job'           => 'required|string|max:255',
            'customer_id'   => 'nullable',
            'payment_term'  => 'nullable',
            // 'invoice_no'      => 'nullable',
            // 'pr_no'         => 'nullable',
            'contract_no'   => 'nullable',

            'items.*.product_id'  => 'required',
            'items.*.qty'         => 'required|numeric|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0'
        ]);

        $so->items()->delete();

        $subtotal = collect($request->items)->sum(fn($i) => $i['qty'] * $i['unit_price']);
        $tax     = $subtotal * 0.11;
        $grand   = $subtotal + $tax;

        $so->update([
            'project_id'=> $request->project_id,
            'job'         => $request->job,
            // 'invoice_no'    => $request->invoice_no,
            'so_date'     => $request->so_date,
            'customer_name' => $request->customer_name,
            'payment_term'=> $request->payment_term,
            // 'pr_no'       => $request->pr_no,
            'contract_no' => $request->contract_no,
            'subtotal'    => $subtotal,
            'tax'         => $tax,
            'grand_total' => $grand,
        ]);

        foreach ($request->items as $item) {
            SalesOrderItem::create([
                'sales_order_id' => $so->id,
                'product_id'        => $item['product_id'],
                'qty'               => $item['qty'],
                'unit_price'        => $item['unit_price'],
                'total_price'       => $item['qty'] * $item['unit_price'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales Order updated.',
            'redirect' => route('sales_orders.index')
        ]);
    }

    public function marginUpdate(Request $request, SalesOrder $so)
    {
        // dd($request->all());

        DB::beginTransaction();
        try {

            foreach ($request->items as $itemId => $row) {

                $item = SalesOrderItem::with('margin')->findOrFail($itemId);
                $margin = $item->margin ?? new OrderCost(['sales_order_item_id' => $item->id]);

                // Ambil value
                $purchase_cost = $row['purchase_cost'] ?? 0;

                $fields = [
                    'contractor_percent','expedition_percent','test_percent',
                    'marketing_percent','trip_percent','salary_percent',
                    'pph_percent','scf_percent','bunga_bank_percent','retensi_percent'
                ];

                $total_percent = 0;
                foreach($fields as $f){
                    $total_percent += ($row[$f] ?? 0);
                }

                // Rumus
                $harga_netto   = ($total_percent/100) * $item->unit_price;
                $total_netto   = $purchase_cost + $harga_netto;
                $margin_value  = $item->unit_price - $total_netto;
                $margin_percent = $item->unit_price > 0 ? ($margin_value/$item->unit_price)*100 : 0;

                // Simpan
                $margin->fill(array_merge($row,[
                    'total_percent'  => $total_percent,
                    'harga_netto'    => $harga_netto,
                    'total_netto'    => $total_netto,
                    'margin_value'   => $margin_value,
                    'margin_percent' => $margin_percent,
                ]));

                // dd($margin);

                $margin->save();
            }

            if($request->pembayaran_items){
                foreach ($request->pembayaran_items['pembayaran_id'] as $key => $value) {
                    // dd($value);
                    PembayaranSalesOrder::create([
                        'sales_order_id' => $so->id,
                        'tanggal_pembayaran' => $request->pembayaran_items['tanggal_pembayaran'][$key],
                        'nominal' => $request->pembayaran_items['pembayaran'][$key],
                    ]);
                }
            }

            if ($request->pembayaran_subs) {
                // dd(json_decode($request->pembayaran_subs));
                foreach (json_decode($request->pembayaran_subs) as $key => $value) {
                    SalesOrderSub::updateOrCreate(
                        [
                            'id' => $value->sub_id ?? null
                        ],
                        [
                            'sales_order_item_id' => $value->item_id,
                            'field' => $value->field,
                            'sub_tanggal' => $value->sub_tanggal,
                            'sub_pembayaran' => $value->sub_pembayaran,
                            'sub_invoice' => $value->sub_invoice,
                        ]
                    );
                }
            }


            DB::commit();

            return response()->json([
                'success'=>true,
                'message'=>'Margin berhasil diperbarui!',
                'redirect' => route('sales_orders.index')
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage()
            ],500);
        }
    }

    public function destroy(SalesOrder $so)
    {
        $so->items()->delete();
        $so->delete();

        return response()->json(['success' => true]);
    }

    public function print(SalesOrder $so)
    {
        $so->load(['items.product', 'customer']);

        // dd($so);

        $pdf = Pdf::loadView('sales_orders.print', [
            'so' => $so,
        ]);

        return $pdf->stream("Sales Order.pdf");
    }

    public function createFromSo(SalesOrder $so)
    {
        $so->load(['items.product', 'invoices.source.items.product', 'invoices.items.sourceItem.product']);

        // dd($so);

        return view('invoices.so-create', compact('so'));
    }

    public function marginSo(SalesOrder $so)
    {
        $so->load(['pembayaran', 'items.product', 'items.margin', 'items.sub']);

        // $items = $so->items->map(function ($item) {
        //     return [
        //         'item_id' => $item->id,
        //         'product' => $item->product->name,

        //         // harga beli per item
        //         'purchase_cost' => $item->margin->purchase_cost ?? 0,

        //         // percent
        //         'contractor_percent' => $item->margin->contractor_percent ?? 0,
        //         'expedition_percent' => $item->margin->expedition_percent ?? 0,
        //         'test_percent'       => $item->margin->test_percent ?? 0,
        //         'marketing_percent'  => $item->margin->marketing_percent ?? 0,
        //         'trip_percent'       => $item->margin->trip_percent ?? 0,
        //         'salary_percent'     => $item->margin->salary_percent ?? 0,
        //         'pph_percent'        => $item->margin->pph_percent ?? 0,
        //         'scf_percent'        => $item->margin->scf_percent ?? 0,
        //         'bunga_bank_percent' => $item->margin->bunga_bank_percent ?? 0,
        //         'retensi_percent'    => $item->margin->retensi_percent ?? 0,
        //     ];

        // });

        $subs = $so->items->flatMap(function ($item) {
            return $item->sub->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'item_id' => $sub->sales_order_item_id,
                    'field' => $sub->field,
                    'sub_tanggal' => $sub->sub_tanggal,
                    'sub_pembayaran' => $sub->sub_pembayaran,
                    'sub_invoice' => $sub->sub_invoice,
                ];
            });
        });

        $subsGrouped = $subs->groupBy(function ($sub) {
            return $sub['item_id'] . '_' . $sub['field'];
        });

        $items = $so->items->map(function ($item) use ($subsGrouped) {

            $fields = [
                'contractor_percent',
                'expedition_percent',
                'test_percent',
                'marketing_percent',
                'trip_percent',
                'salary_percent',
                'pph_percent',
                'scf_percent',
                'bunga_bank_percent',
                'retensi_percent',
            ];

            $result = [
                'item_id' => $item->id,
                'product' => $item->product->name,

                'purchase_cost' => $item->margin->purchase_cost ?? 0,
            ];

            foreach ($fields as $field) {
                $key = $item->id . '_' . $field;

                $result[$field] = [
                    'percent' => $item->margin->$field ?? 0,
                    'subs' => $subsGrouped[$key] ?? [],
                ];
            }

            return $result;
        });

        // dd($items);

        $pembayaran = $so->pembayaran->map(function($p){
            return [
                'id' => $p->id,
                'tanggal_pembayaran' => $p->tanggal_pembayaran,
                'nominal' => $p->nominal,
                'sub_invoice' => $p->sub_invoice
            ];
        });


        return view('sales_orders.margin', compact('so', 'items', 'pembayaran', 'subs'));
    }

    public function soItems($project_id)
    {

        // dd($project_id);
        $so = SalesOrder::where('project_id', $project_id)->first();

        return $so->items()->with('product')->get()->map(function ($item) {
            // dd($item);
            return [
                'product_id'   => $item->product_id,
                'product_name' => $item->product->name,
                'qty'          => $item->qty,
                'unit_price'    => $item->unit_price,
            ];
        });
    }

    public function export()
    {
        return Excel::download(new SalesOrderExport, 'sales_orders.xlsx');
    }

    public function marginExportSo(SalesOrder $so)
    {
        // dd($so);
        return Excel::download(new MarginExport($so), 'so-margin.xlsx');
    }

    public function excelSo(SalesOrder $so)
    {
        // dd();
        return Excel::download(new SalesOrderExport, 'sales_orders.xlsx');
    }

}
