@extends('layouts.app')

@section('title', 'Create Invoice')

@push('styles')
@endpush

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Create Invoice - SO {{ $so->so_number }}</h5>
        <a href="{{ route('sales_orders.index') }}" class="btn btn-secondary">Back</a>
    </div>


    <div class="card-body">

        {{-- <div class="row mb-2">
            @foreach ($so->invoices as $item)
            <div class="col-md-3">
                <a class="btn btn-primary col-md-12" href="/invoices/{{ $item->id }}/print">{{ $item->invoice_number }}</a>
            </div>
            @endforeach
        </div> --}}

        <form id="editForm" data-id="{{ $so->id }}">

            @csrf
            @method('PUT')

            <div class="row mb-3">

                <div class="col-md-4">
                    <label>SO Number</label>
                    <input type="text" class="form-control" value="{{ $so->so_number }}" disabled>
                    <input type="hidden" class="form-control" name="po_id" value="{{ $so->id }}" disabled>
                </div>

                <div class="col-md-4">
                    <label>Job Project</label>
                    <select name="project_id" id="projectSelect" class="form-control" disabled></select>
                </div>

                {{-- <div class="col-md-4">
                    <label>Customer</label>
                    <select name="customer_id" id="customerSelect" class="form-control" disabled></select>
                </div> --}}

                <div class="col-md-4">
                    <label>Contract No</label>
                    <input type="text" name="contract_no" class="form-control" value="{{ $so->contract_no }}" disabled>
                </div>

                <div class="col-md-4">
                    <label>Project</label>
                    <input type="text" name="job" class="form-control" value="{{ $so->project->name }}" disabled>
                </div>

                {{-- <div class="col-md-4">
                    <label>Purchase Request</label>
                    <select name="pr_id" id="prSelect" class="form-control" disabled></select>
                </div> --}}

                {{-- <div class="col-md-4">
                    <label>Order No</label>
                    <input type="text" name="order_no" class="form-control" value="{{ $so->order_no }}" required>
                </div> --}}


                <div class="col-md-4">
                    <label>SO Date</label>
                    <input type="text" name="so_date" id="so_date" class="form-control" value="{{ $so->so_date }}" placeholder="DD-MM-YY" disabled>
                </div>

                <div class="col-md-4">
                    <label>Invoice Number</label>
                    <input type="text" name="inv_number" class="form-control" value="" required>
                </div>

                <div class="col-md-4">
                    <label>Invoice Date</label>
                    <input type="text" name="inv_date" id="inv_date" class="form-control" value="" placeholder="DD-MM-YY" required>
                </div>
                <div class="col-md-4">
                    <label>No. Faktur</label>
                    <input type="text" name="no_faktur" id="no_faktur" class="form-control" value="" placeholder="No. Faktur" required>
                </div>

                <div class="col-md-4">
                    <label>TOP</label>
                    <input type="number" name="top" class="form-control" value="" required>
                </div>


            </div>

            <hr>
            <h6>Items</h6>

            <table class="table table-bordered" id="itemTable">
                <thead>
                    <tr>
                        <th width="35%">Product</th>
                        <th>Qty SO</th>
                        <th>Qty Sisa</th>
                        <th>Qty Inv</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        {{-- <th width="5%"> --}}
                            {{-- <button type="button" class="btn btn-success btn-sm" id="addRow">+</button> --}}
                        {{-- </th> --}}
                    </tr>
                </thead>

                <tbody>
                    @foreach($so->items as $item)
                    <tr>
                        <td>
                            <input type="text" class="form-control qty" value="{{ $item->product->name }}" readonly>
                            <input type="hidden" class="form-control po_item_id" value="{{ $item->id }}" readonly>
                        </td>
                        <td><input type="number" name="" class="form-control" value="{{ $item->qty }}" step="any" readonly></td>
                        <td><input type="number" name="items[][qty]" class="form-control qty" value="{{ $item->qty - $item->qty_invoiced }}" step="any" readonly></td>
                        <td><input type="number" name="items[][qty_inv]" class="form-control qty_inv" max="{{ $item->qty - $item->qty_invoiced }}" step="any" required></td>
                        <td><input type="number" name="items[][unit_price]" class="form-control price" value="{{ $item->unit_price }}" step="any" readonly></td>
                        <td class="total">{{ number_format($item->total_price) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="text-end">
                <p>Subtotal: <span id="subtotal">{{ number_format($so->subtotal) }}</span></p>
                <p>PPN (11%): <span id="tax">{{ number_format($so->tax) }}</span></p>
                <h5>Grand Total: <span id="grand_total">{{ number_format($so->grand_total) }}</span></h5>
            </div>

            @if ($so->status != 'closed')
                <button class="btn btn-primary mt-3">Create Invoice</button>
            @endif

        </form>
    </div>
</div>

<div class="card mt-2">
    <div class="card-header">
        <h3>INVOICE</h3>
    </div>
    <div class="card-body">
        <a href="javascript:void(0)" class="btn btn-success mt-3 simpan-payment">Update Invoice</a>
        <div class="list-group">
            <table class="table table-sm pembayaran-table" data-item="{{ $item->id }}">
                <thead>
                    <tr>
                        <th width="35%">Invoice</th>
                        <th>Piutang</th>
                        <th>
                            Qty Inv
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @foreach($so->items as $soi) --}}
                    {{-- @php
                        dump($soi);
                    @endphp --}}
                    @foreach ($so->invoices as $inv)
                    {{-- @if ($soi->sales_order_id == $inv->source_id) --}}
                    <tr>
                        <td>
                            <a href="/invoices/{{ $inv->id }}/print" class="mt-2 list-group-item list-group-item-action">{{ $inv->invoice_number }}</a>
                        </td>
                        <td>
                            <a href="/invoices/{{ $inv->id }}/piutang" class="mt-2 list-group-item list-group-item-action" style="margin-left: 10px">Piutang</a>
                        </td>
                        <td class="">

                            @if ($inv->items->isNotEmpty())
                                @foreach ($inv->items as $invItem)
                                <div class="inv-item row">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" value="{{ $invItem->sourceItem->product->name }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control invItem_qty" value="{{ $invItem->qty }}" step="any">
                                    </div>
                                    <input type="hidden" class="form-control invItem_id" value="{{ $invItem->id }}">
                                    <input type="hidden" class="form-control soItem_id" value="{{ $invItem->source_item_id }}">
                                    <input type="hidden" class="form-control inv_id" value="{{ $inv->id }}">
                                </div>
                                @endforeach
                            @else
                                @foreach ($inv->source->items as $item)
                                <div class="inv-item row">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" value="{{ $item->product->name }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control invItem_qty" value="0" step="any">
                                    </div>
                                    <input type="hidden" class="form-control invItem_id" value="0">
                                    <input type="hidden" class="form-control soItem_id" value="{{ $item->id }}">
                                    <input type="hidden" class="form-control inv_id" value="{{ $inv->id }}">
                                </div>

                                @endforeach
                            @endif


                        </td>
                    </tr>
                    {{-- @endif
                    @endforeach --}}
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>

let customerChoices;

// ========================
// LOAD SUPPLIER (AJAX)
// ========================
function loadCustomers(selectedId = null) {
    $.get('/ajax/customers', function (res) {

        const customerChoices = new Choices('#customerSelect', {
            shouldSort: false,
        });

        customerChoices.clearChoices();

        customerChoices.setChoices(
            res.map(s => ({
                value: s.id,
                label: s.name,
                selected: selectedId ? s.id == selectedId : false
            })),
            'value',
            'label',
            true
        );
    });
}

function loadProjects(selectedId = null) {
    $.get('/ajax/projects', function (res) {

        const projectChoices = new Choices('#projectSelect', {
            shouldSort: false,
        });

        projectChoices.clearChoices();

        projectChoices.setChoices(
            res.map(s => ({
                value: s.id,
                label: s.job,
                selected: selectedId ? s.id == selectedId : false
            })),
            'value',
            'label',
            true
        );
    });
}

function loadPurchaseRequests(project_id = null, selectedId = null) {
    console.log(project_id);
    console.log(selectedId);
    $.get('/ajax/prs', {project_id : project_id}, function (res) {

        const prChoices = new Choices('#prSelect', {
            shouldSort: false,
        });

        prChoices.clearChoices();

        prChoices.setChoices(
            res.map(s => ({

                value: s.id,
                label: s.pr_number,
                selected: selectedId ? s.id == selectedId : false
            })),
            'value',
            'label',
            true
        );
    });
}


// ========================
// LOAD PRODUCTS (FOR EACH ROW)
// ========================
function loadProducts(selectEl, selectedId = null) {

    const productChoice = new Choices(selectEl, {
        shouldSort: false,
    });

    productChoice.clearChoices();

    $.get('/ajax/products', function (res) {

        productChoice.setChoices(
            res.map(p => ({
                value: p.id,
                label: p.name,
                selected: selectedId ? p.id == selectedId : false
            })),
            'value',
            'label',
            true
        );

    });
}

function loadPrItems(prId) {

    $("#itemTable tbody").empty(); // reset item

    $.get(`/ajax/prs/${prId}/items`, function (items) {

        items.forEach(item => {
            addRowFromPR(item);
        });

        calculate();
    });
}

function addRowFromPR(item) {

    let row = `
    <tr>
        <td>
            <select name="items[][product_id]" class="form-control productSelect">
                <option value="${item.product_id}" selected>
                    ${item.product_name}
                </option>
            </select>
        </td>
        <td>
            <input type="number" name="items[][qty]" class="form-control qty" value="${item.qty}">
        </td>
        <td>
            <input type="number" name="items[][unit_price]" class="form-control price" value="${item.unit_price}">
        </td>
        <td class="total">0</td>
        <td>
            <button type="button" class="btn btn-danger btn-sm removeRow">x</button>
        </td>
    </tr>
    `;

    $("#itemTable tbody").append(row);

    let select = $("#itemTable tbody tr:last .productSelect")[0];

    // inject choices (1 item saja, supaya lock PR)
    new Choices(select, {
        searchEnabled: false,
        itemSelectText: '',
        shouldSort: false
    });
}

$('.simpan-payment').on('click', function () {

    let items = [];


    $('.pembayaran-table').find('.inv-item').each(function () {
        items.push({
            invItem_id: $(this).find('.invItem_id').val(),
            inv_id: $(this).find('.inv_id').val(),
            soItem_id: $(this).find('.soItem_id').val(),
            invItem_qty: $(this).find('.invItem_qty').val(),
        });
    });

    $.ajax({
        url: "{{ route('invoices.updateItems') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            items: items,
            id : $("#editForm").data('id')
        },
        success: function (res) {
            toastSuccess(res.message);

            location.reload();
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                let err = xhr.responseJSON.errors;
                let first = Object.values(err)[0][0];
                toastError(first, "error");
            } else {
                toastError("Server error", "error");
            }
        }
    });
});

$(document).ready(function () {

    // load customers
    // loadCustomers({{ $so->customer_id }});
    loadProjects({{ $so->project_id }});
    loadPurchaseRequests({{ $so->project_id }}, {{ $so->purchase_request_id }});

    // load products for existing rows
    $(".productSelect").each(function () {
        let selected = $(this).data('selected');
        loadProducts(this, selected);
    });

    $('#prSelect').on('change', function () {
        let prId = $(this).val();
        if (!prId) return;

        loadPrItems(prId);
    });

    flatpickr("#so_date", {
        dateFormat: "Y-m-d",      // format yg dikirim ke backend
        altInput: true,           // tampilkan format ramah user
        altFormat: "d-m-Y",       // contoh: 14 September 2025
        allowInput: true
    });

    flatpickr("#inv_date", {
        dateFormat: "Y-m-d",      // format yg dikirim ke backend
        altInput: true,           // tampilkan format ramah user
        altFormat: "d-m-Y",       // contoh: 14 September 2025
        allowInput: true
    });

    // add new row
    $("#addRow").click(function () {
        let tr = `
        <tr>
            <td><select name="items[][product_id]" class="form-control productSelect"></select></td>
            <td><input type="number" name="items[][qty]" class="form-control qty" value="1"></td>
            <td><input type="number" name="items[][unit_price]" class="form-control price" value="0"></td>
            <td class="total">0</td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">x</button></td>
        </tr>`;
        $("#itemTable tbody").append(tr);

        let last = $("#itemTable tbody tr:last .productSelect")[0];
        loadProducts(last);
        calculate();
    });

    // remove row
    $(document).on("click", ".removeRow", function () {
        $(this).closest("tr").remove();
        calculate();
    });

    // recalc
    $(document).on("input", ".qty, .price", calculate);

});

// ======================
// CALCULATE TOTAL
// ======================
function calculate() {
    let subtotal = 0;

    $("#itemTable tbody tr").each(function () {
        let qty = parseFloat($(this).find(".qty").val()) || 0;
        let price = parseFloat($(this).find(".price").val()) || 0;
        let total = qty * price;

        $(this).find(".total").text(total.toLocaleString());
        subtotal += total;
    });

    let tax = subtotal * 0.11;
    let grand = subtotal + tax;

    $("#subtotal").text(subtotal.toLocaleString());
    $("#tax").text(tax.toLocaleString());
    $("#grand_total").text(grand.toLocaleString());
}

// =====================
// AJAX SUBMIT UPDATE
// =====================
$("#editForm").submit(function (e) {
    e.preventDefault();

    let id = $(this).data('id');

    let formData = new FormData();

    formData.append('_token', "{{ csrf_token() }}");
    formData.append('_method', "POST"); // kalau mau PUT ganti "PUT"

    formData.append('contract_no', $("input[name='contract_no']").val());
    formData.append('job', $("input[name='job']").val());
    formData.append('top', $("input[name='top']").val());
    formData.append('no_faktur', $("input[name='no_faktur']").val());
    formData.append('so_date', $("input[name='so_date']").val());
    formData.append('inv_date', $("input[name='inv_date']").val());
    formData.append('inv_number', $("input[name='inv_number']").val());
    formData.append('po_id', $("input[name='po_id']").val());
    formData.append('customer_id', $("#customerSelect").val());
    formData.append('project_id', $("#projectSelect").val());
    formData.append('pr_id', $("#prSelect").val());
    formData.append('type', 'SalesOrder');

    // =========================
    // ITEMS
    // =========================
    let items = [];

    $("#itemTable tbody tr").each(function () {
        let p = $(this).find(".productSelect").val();
        let q = $(this).find(".qty").val();
        let qi = $(this).find(".qty_inv").val();
        let poi = $(this).find(".po_item_id").val();
        let u = $(this).find(".price").val();

        items.push({
            product_id: p,
            qty: q,
            qty_inv: qi,
            po_item_id: poi,
            unit_price: u
        });
    });

    formData.append('items', JSON.stringify(items));


    $.ajax({
        url: "{{ route('invoices.store') }}",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function (res) {
            toastSuccess(res.message);

            setTimeout(() => {
                window.location.href = res.redirect;
            }, 800);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                let err = xhr.responseJSON.errors;
                let first = Object.values(err)[0][0];
                toastError(first, "error");
            } else {
                toastError("Server error", "error");
            }
        }
    });
});
</script>
@endpush
