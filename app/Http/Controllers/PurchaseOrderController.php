<?php

namespace App\Http\Controllers;

use App\Exports\purchaseOrderExport;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $pos = PurchaseOrder::with('supplier')->latest()->get();
        return view('purchase_orders.index', compact('pos'));
    }

    public function data()
    {
        $pos = PurchaseOrder::with('supplier', 'project')
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'data' => $pos
        ]);
    }


    public function create()
    {
        return view('purchase_orders.create', [
            'suppliers' => Supplier::all(),
            'products'  => Product::all(),
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'po_number'     => 'required|unique:purchase_orders,po_number',
            'po_date'       => 'required|date',
            'project_id'  => 'required|string|max:255',
            'job'           => 'required|string|max:255',
            'order_no'      => 'required|string|max:255',
            'contract_no'   => 'nullable',
            'pr_id'         => 'nullable',
            'supplier_id'   => 'required',
            'payment_term'  => 'nullable',
            'order_no'      => 'nullable',

            'items.*.product_id'  => 'required',
            'items.*.qty'         => 'required|numeric|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0'
        ]);

        // hitung subtotal
        $subtotal = collect($request->items)->sum(fn($i) => $i['qty'] * $i['unit_price']);
        $tax = $subtotal * 0.11;
        $grand = $subtotal + $tax;

        $po = PurchaseOrder::create([
            'po_number'   => $request->po_number,
            'project_id' => $request->project_id,
            'job'         => $request->job,
            'order_no'    => $request->order_no,
            'purchase_request_id'       => $request->pr_id,
            'po_date'     => $request->po_date,
            'supplier_id' => $request->supplier_id,
            'payment_term' => $request->payment_term,
            'contract_no' => $request->contract_no,
            'subtotal'    => $subtotal,
            'tax'         => $tax,
            'grand_total' => $grand,
        ]);

        foreach ($request->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id'        => $item['product_id'],
                'qty'               => $item['qty'],
                'unit_price'        => $item['unit_price'],
                'total_price'       => $item['qty'] * $item['unit_price'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order created.',
            'redirect' => route('purchase_orders.index')
        ]);
    }

    public function edit(PurchaseOrder $po)
    {
        return view('purchase_orders.edit', [
            'po'        => $po->load('items'),
            'suppliers' => Supplier::all(),
            'products'  => Product::all(),
        ]);
    }

    public function update(Request $request, PurchaseOrder $po)
    {
        $validated = $request->validate([
            'po_date'       => 'required',
            // 'project_name'  => 'required|string|max:255',
            'job'           => 'required|string|max:255',
            'supplier_id'   => 'required',
            'project_id'   => 'required',
            'pr_id'   => 'required',
            'payment_term'  => 'nullable',
            'order_no'      => 'nullable',
            'pr_no'         => 'nullable',
            'contract_no'   => 'nullable',

            'items.*.product_id'  => 'required',
            'items.*.qty'         => 'required|numeric|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0'
        ]);

        $po->items()->delete();

        $subtotal = collect($request->items)->sum(fn($i) => $i['qty'] * $i['unit_price']);
        $tax     = $subtotal * 0.11;
        $grand   = $subtotal + $tax;

        $po->update([
            'project_id' => $request->project_id,
            'job'         => $request->job,
            // 'order_no'    => $request->order_no,
            'po_date'     => $request->po_date,
            'supplier_id' => $request->supplier_id,
            'purchase_request_id' => $request->pr_id,
            'payment_term' => $request->payment_term,
            // 'pr_no'       => $request->pr_no,
            'contract_no' => $request->contract_no,
            'subtotal'    => $subtotal,
            'tax'         => $tax,
            'grand_total' => $grand,
        ]);

        foreach ($request->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'product_id'        => $item['product_id'],
                'qty'               => $item['qty'],
                'unit_price'        => $item['unit_price'],
                'total_price'       => $item['qty'] * $item['unit_price'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase Order updated.',
            'redirect' => route('purchase_orders.index')
        ]);
    }

    public function destroy(PurchaseOrder $po)
    {
        $po->items()->delete();
        $po->delete();

        return response()->json(['success' => true]);
    }


    public function show(PurchaseOrder $po)
    {
        return view('purchase_orders.show', [
            'po' => $po->load('items', 'supplier')
        ]);
    }

    public function print(PurchaseOrder $po)
    {
        $po->load('supplier', 'items.product', 'project', 'purchaseRequest');

        // data tambahan yg ingin ditampilkan
        $data = [
            'po' => $po,
            'company' => [
                'name' => 'PT. DELTA SYSTECH INDONESIA',
                'address1' => 'Wisma Ritra 2nd Floor, Jl. Warung Buncit Raya No. 6, Jakarta 12740',
                'branch' => 'Surabaya Branch - Ruko Sentraland Blok B-38, Kota Baru Driyorejo Gresik'
            ],
            'generated_at' => now()->format('d-M-Y'),
        ];

        $pdf = Pdf::loadView('purchase_orders.print', $data);
        // jika ingin landscape: ->setPaper('a4', 'landscape')
        return $pdf->stream("Purchase Order.pdf");
    }

    public function ajaxSuppliers()
    {
        return Supplier::select('id', 'name')->orderBy('name')->get();
    }

    public function ajaxCustomers()
    {
        return Customer::select('id', 'name')->orderBy('name')->get();
    }

    public function ajaxProducts()
    {
        return Product::select('id', 'name')->orderBy('name')->get();
    }

    public function ajaxProjects()
    {
        return Project::select('id', 'name', 'job', 'contract_no', 'customer_name')->orderBy('name')->get();
    }

    public function ajaxPrs(Request $request)
    {
        // dd($request->all());
        return PurchaseRequest::select('id', 'pr_number')
            ->where('project_id', $request->project_id)
            ->orderBy('pr_number')->get();
    }

    public function prItems(PurchaseRequest $pr)
    {
        return $pr->items()->with('product')->get()->map(function ($item) {
            return [
                'product_id'   => $item->product_id,
                'product_name' => $item->product->name,
                'qty'          => $item->qty,
                'unit_price'    => $item->unit_price,
            ];
        });
    }

    public function createFromPo(PurchaseOrder $po)
    {
        $po->load(['items.product', 'invoices', 'invoices.items', 'invoices.poPayments']);

        $po->invoices->flatMap(function ($invoice) {
            return $invoice->poPayments->map(function ($p) {
                return [
                    'id' => $p->id,
                    'sub_tanggal' => $p->sub_tanggal,
                    'sub_pembayaran' => $p->sub_pembayaran,
                ];
            });
        });


        $total = $po->invoices->sum('grand_total');



        // dd($po);

        return view('invoices.po-create', compact('po'));
    }

    public function export()
    {
        return Excel::download(new purchaseOrderExport, 'purchase_orders.xlsx');
    }
}
