<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Piutang;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderPayment;
use App\Models\PurchaseRequest;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd('asd');
        // dd($request->all());
        if ($request->type == 'PurchaseOrder') {
            $redirect = route('purchase_orders.index');
        }else{
            $redirect = route('sales_orders.index');
        }

        DB::transaction(function () use ($request) {

            if ($request->type == 'PurchaseOrder') {
                $po = PurchaseOrder::with('items')->findOrFail($request->po_id);
                $class = PurchaseOrder::class;
                $classItem = PurchaseOrderItem::class;
            }else{
                $po = SalesOrder::with('items')->findOrFail($request->po_id);
                $class = SalesOrder::class;
                $classItem = SalesOrderItem::class;
            }

            if ($po->status === 'closed') {
                throw new \Exception('Sudah Lunas');
            }

            $top = (int) $request->top;

            $path = null; // ✅ default

            if ($request->hasFile('lampiran')) {
                $file = $request->file('lampiran');

                $ext = $file->getClientOriginalExtension();
                $name = $request->inv_number . '_' . Str::random(8) . '.' . $ext;

                $path = $file->storeAs('lampiran', $name, 'public');
            }

            // $existingInvoice = Invoice::where('invoice_number', $request->inv_number)->first();

            $invoice = Invoice::create([
                'invoice_number' => $request->inv_number,
                'top' => $top,
                'no_faktur' => $request->no_faktur,
                'invoice_date' => $request->inv_date,
                'jatuh_tempo' => Carbon::parse($request->inv_date)->addDays($top),
                'type' => $request->type,
                'source_type' => $class,
                'source_id' => $po->id,
                'subtotal' => 0,
                'tax' => 0,
                'grand_total' => 0,
                'lampiran' => $path,
            ]);



            $subtotal = 0;


            foreach (json_decode($request->items, true) as $poItemId => $row) {

                if ($row['qty_inv'] <= 0) continue;

                if ($request->type == 'PurchaseOrder') {
                    $poItem = PurchaseOrderItem::findOrFail($row['po_item_id']);

                }else{
                    $poItem = SalesOrderItem::findOrFail($row['po_item_id']);
                }

                $sisa = $poItem->qty - $poItem->qty_invoiced;

                if ($row['qty_inv'] > $sisa) {
                    throw new Exception('Qty invoice melebihi sisa');
                }

                // stock update
                $product = Product::findOrFail($poItem->product_id);

                if ($request->type == 'PurchaseOrder') {
                    $product->increment('qty', $row['qty_inv']);
                }else{
                    $product->increment('qty', -$row['qty_inv']);
                }

                $total = $row['qty_inv'] * $row['unit_price'];

                InvoiceItem::create([
                    'source_item_type' => $classItem,
                    'source_item_id' => $poItem->id,

                    'invoice_id' => $invoice->id,
                    // 'purchase_order_item_id' => $poItem->id,
                    'qty' => $row['qty_inv'],
                    'unit_price' => $row['unit_price'],
                    'total' => $total,
                ]);

                // update qty invoiced
                $poItem->increment('qty_invoiced', $row['qty_inv']);

                $subtotal += $total;
            }

            $tax = $subtotal * 0.11;

            $invoice->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'grand_total' => $subtotal + $tax
            ]);

            // update status PO

            if ($request->type == 'PurchaseOrder') {
                $po = PurchaseOrder::with('items')->findOrFail($request->po_id);
                $pr = PurchaseRequest::with('items')->findOrFail($po->purchase_request_id);

                $totalOrdered = $po->items->sum('qty');
                $totalInvoiced = $po->items->sum('qty_invoiced');


                $po->update([
                    'status' => $totalInvoiced >= $totalOrdered
                    ? 'closed'
                    : 'partial'
                ]);

                $pr->update([
                    'status' => $totalInvoiced >= $totalOrdered
                        ? 'closed'
                        : 'partial'
                ]);
            }else{
                $po = SalesOrder::with('items')->findOrFail($request->po_id);

                $totalOrdered = $po->items->sum('qty');
                $totalInvoiced = $po->items->sum('qty_invoiced');


                $po->update([
                    'status' => $totalInvoiced >= $totalOrdered
                    ? 'closed'
                    : 'partial'
                ]);
            }



        });

        return response()->json([
            'success' => true,
            'message' => 'Invoice dibuat.',
            'redirect' => $redirect
        ]);
    }

    public function updateItems(Request $request)
    {
        // dd($request->all());

        DB::transaction(function () use ($request) {

            $items = $request->input('items', []);

            $qty = [];
            foreach ($items as $item) {
                $invItem = InvoiceItem::find($item['invItem_id']);

                if (!isset($qty[$item['soItem_id']])) {
                    $qty[$item['soItem_id']] = 0;
                }

                if ($invItem) {

                    $qty[$item['soItem_id']] += $item['invItem_qty'];

                    $invItem->update([
                        'qty' => $item['invItem_qty'],
                    ]);
                }else{
                    $soItem = SalesOrderItem::findOrFail($item['soItem_id']);

                    $qty[$item['soItem_id']] += $item['invItem_qty'];

                    $invItem = new InvoiceItem();
                    $invItem->source_item_type = SalesOrderItem::class;
                    $invItem->source_item_id = $item['soItem_id'];
                    $invItem->invoice_id = $item['inv_id'];
                    $invItem->qty = $item['invItem_qty'];
                    $invItem->unit_price = $soItem->unit_price;
                    $invItem->total = $item['invItem_qty'] * $soItem->unit_price;
                    $invItem->save();

                }

            }

            // dd($qty);

            foreach ($qty as $soItemId => $invItemQty) {
                $soItem = SalesOrderItem::findOrFail($soItemId);

                $totalOrdered = $soItem->qty;
                $totalInvoiced = $invItemQty;

                $soItem->update([
                    'qty_invoiced' => $totalInvoiced
                ]);

                // dd($totalInvoiced, $totalOrdered);

                $so = SalesOrder::findOrFail($soItem->sales_order_id);

                $so->update([
                    'status' => $totalInvoiced >= $totalOrdered
                    ? 'closed'
                    : 'partial'
                ]);
            }
        });



        return response()->json([
            'success' => true,
            'message' => 'Items updated successfully.',
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }

    public function print(Invoice $inv)
    {
        if ($inv->type == 'PurchaseOrder') {
            $inv->load(['source', 'source.project', 'source.supplier', 'source.purchaseRequest' , 'items', 'items.sourceItem', 'items.sourceItem.product']);

            $pdf = Pdf::loadView('invoices.po-print', [
                'inv' => $inv,
            ]);

            return $pdf->stream("PO-Invoice.pdf");
        }else{
            $inv->load(['source', 'source.project', 'source.customer' , 'items', 'items.sourceItem', 'items.sourceItem.product']);

            $pdf = Pdf::loadView('invoices.so-print', [
                'inv' => $inv,
            ]);

            return $pdf->stream("SO-Invoice.pdf");
        }

    }

    public function poPembayaran(Request $request)
    {

        DB::beginTransaction();
        try {

            // dd($request->all());
            if ($request->pembayaran) {
                foreach ($request->pembayaran as $key => $value) {
                    // dd($value);
                    PurchaseOrderPayment::updateOrCreate(
                        [
                            'id' => $value['sub_id'] ?? null
                        ],
                        [
                            'invoice_id' => $value['item_id'],
                            'sub_tanggal' => $value['sub_tanggal'],
                            'sub_pembayaran' => $value['sub_pembayaran'],
                        ]
                    );

                }
            }

            if ($request->inv) {
                // dd($request->inv);
                foreach ($request->inv as $key => $value) {
                    // dd($value);
                    Invoice::find($value['id_inv'])->update([
                        'grand_total' => $value['gt_inv']
                    ]);
                }
            }


            DB::commit();

            return response()->json([
                'success'=>true,
                'message'=>'Margin berhasil diperbarui!',
                // 'redirect' => route('sales_orders.index')
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success'=>false,
                'message'=>$e->getMessage()
            ],500);
        }
    }

    public function piutangSo(Invoice $inv)
    {
        $inv->load(['piutangs']);
        $totalBayar = (float) $inv->piutangs->sum('amount');
        $sisaPiutang = max(0, (float) $inv->grand_total - $totalBayar);

        return view('invoices.so-piutang', compact('inv', 'totalBayar', 'sisaPiutang'));
    }

    public function piutangStore(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:invoices,id',
            'items' => 'required|array|min:1',
        ]);

        $redirect = url('/invoices/' . $request->id . '/piutang');

        return DB::transaction(function () use ($request, $redirect) {
            $invoice = Invoice::with('piutangs')->lockForUpdate()->findOrFail($request->id);

            $alreadyPaid = (float) $invoice->piutangs->sum('amount');
            $sisaPiutang = max(0, (float) $invoice->grand_total - $alreadyPaid);

            // Filter item yang memiliki nominal > 0 dan piutang_date
            $validItems = [];
            $newPaymentTotal = 0;

            foreach ($request->items as $row) {
                $amount = isset($row['amount']) ? (float) $row['amount'] : 0;
                $date = !empty($row['piutang_date']) ? $row['piutang_date'] : null;

                if ($amount > 0 && $date) {
                    $validItems[] = [
                        'invoice_id' => $invoice->id,
                        'date' => $date,
                        'amount' => $amount,
                        'bank' => $row['bank'] ?? null,
                        'note' => $row['notes'] ?? null,
                    ];
                    $newPaymentTotal += $amount;
                }
            }

            if (empty($validItems)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data pembayaran yang valid untuk disimpan.',
                ], 422);
            }

            if ($newPaymentTotal > ($sisaPiutang + 0.01)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total pembayaran baru (Rp ' . number_format($newPaymentTotal, 0, ',', '.') . ') melebihi sisa piutang (Rp ' . number_format($sisaPiutang, 0, ',', '.') . ').',
                ], 422);
            }

            foreach ($validItems as $itemData) {
                Piutang::create($itemData);
            }

            // Update status invoice jika sudah lunas
            $totalSemuaBayar = $alreadyPaid + $newPaymentTotal;
            if ($totalSemuaBayar >= $invoice->grand_total) {
                $invoice->update(['status' => 'closed']);
            } elseif ($totalSemuaBayar > 0 && $invoice->status === 'draft') {
                $invoice->update(['status' => 'partial']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran piutang berhasil disimpan.',
                'redirect' => $redirect
            ]);
        });
    }
}
