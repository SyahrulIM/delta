<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $prs = PurchaseRequest::with('project')->latest()->get();
        return view('purchase_requests.index', compact('prs'));
    }

    public function data()
    {
        $prs = PurchaseRequest::with('project')
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json([
            'data' => $prs
        ]);
    }


    public function create()
    {
        return view('purchase_requests.create', [
            'suppliers' => Supplier::all(),
            'products'  => Product::all(),
        ]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'pr_number'     => 'required|unique:purchase_requests,pr_number',
            'pr_date'       => 'required|date',
            'contract_no'   => 'required|string|max:255',
            'project_id'    => 'required|string|max:255',
            'job'           => 'required|string|max:255',

            'items.*.product_id'  => 'required',
            'items.*.qty'         => 'required|numeric|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0'
        ]);

        // hitung subtotal
        $subtotal = collect($request->items)->sum(fn($i) => $i['qty'] * $i['unit_price']);
        $tax = $subtotal * 0.11;
        $grand = $subtotal + $tax;

        $pr = PurchaseRequest::create([
            'pr_number'   => $request->pr_number,
            'pr_date'     => $request->pr_date,
            'project_id'  => $request->project_id,
            'job'         => $request->job,
            'contract_no' => $request->contract_no,
            'subtotal'    => $subtotal,
            'tax'         => $tax,
            'grand_total' => $grand,
        ]);

        foreach ($request->items as $item) {
            PurchaseRequestItem::create([
                'purchase_request_id' => $pr->id,
                'product_id'        => $item['product_id'],
                'qty'               => $item['qty'],
                'unit_price'        => $item['unit_price'],
                'total_price'       => $item['qty'] * $item['unit_price'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase Request created.',
            'redirect' => route('purchase_requests.index')
        ]);
    }

    public function edit(PurchaseRequest $pr)
    {
        return view('purchase_requests.edit', [
            'pr'        => $pr->load('items'),
            'projects' => Project::all(),
            'products'  => Product::all(),
        ]);
    }

    public function update(Request $request, PurchaseRequest $pr)
    {
        $validated = $request->validate([
            'pr_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('purchase_requests', 'pr_number')->ignore($pr->id)
            ],
            'pr_date'       => 'required',
            'project_id'  => 'required|string|max:255',
            'job'           => 'required|string|max:255',
            'contract_no'   => 'nullable',

            'items.*.product_id'  => 'required',
            'items.*.qty'         => 'required|numeric|min:1',
            'items.*.unit_price'  => 'required|numeric|min:0'
        ]);

        $pr->items()->delete();

        $subtotal = collect($request->items)->sum(fn($i) => $i['qty'] * $i['unit_price']);
        $tax     = $subtotal * 0.11;
        $grand   = $subtotal + $tax;

        $pr->update([
            'pr_number'   => $request->pr_number,
            'pr_date'     => $request->pr_date,
            'project_id'  => $request->project_id,
            'job'         => $request->job,
            'contract_no' => $request->contract_no,
            'subtotal'    => $subtotal,
            'grand_total' => $grand,
        ]);

        foreach ($request->items as $item) {
            PurchaseRequestItem::create([
                'purchase_request_id' => $pr->id,
                'product_id'        => $item['product_id'],
                'qty'               => $item['qty'],
                'unit_price'        => $item['unit_price'],
                'total_price'       => $item['qty'] * $item['unit_price'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Purchase Request updated.',
            'redirect' => route('purchase_requests.index')
        ]);
    }

    public function destroy(PurchaseRequest $pr)
    {
        $pr->items()->delete();
        $pr->delete();

        return response()->json(['success' => true]);
    }


    public function show(PurchaseRequest $pr)
    {
        return view('purchase_requests.show', [
            'po' => $pr->load('items', 'supplier')
        ]);
    }

    public function print(PurchaseRequest $pr)
    {
        $pr->load('project', 'items.product', 'po');

        // data tambahan yg ingin ditampilkan
        $data = [
            'pr' => $pr,
            'company' => [
                'name' => 'PT. DELTA SYSTECH INDONESIA',
                'address1' => 'Wisma Ritra 2nd Floor, Jl. Warung Buncit Raya No. 6, Jakarta 12740',
                'branch' => 'Surabaya Branch - Ruko Sentraland Blok B-38, Kota Baru Driyorejo Gresik'
            ],
            'generated_at' => now()->format('d-M-Y'),
        ];

        $pdf = Pdf::loadView('purchase_requests.print', $data);
        // jika ingin landscape: ->setPaper('a4', 'landscape')
        return $pdf->stream("Purchase Request.pdf");
    }

    public function ajaxSuppliers()
    {
        return Supplier::select('id','name')->orderBy('name')->get();
    }

    public function ajaxCustomers()
    {
        return Customer::select('id','name')->orderBy('name')->get();
    }

    public function ajaxProducts()
    {
        return Product::select('id','name')->orderBy('name')->get();
    }
}
