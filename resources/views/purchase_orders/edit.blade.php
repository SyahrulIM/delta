@extends('layouts.app')

@section('title', 'Edit Purchase Order')

@push('styles')
@endpush

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Edit Purchase Order</h5>
        <a href="{{ route('purchase_orders.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card-body">

        <form id="editForm" data-id="{{ $po->id }}">

            @csrf
            @method('PUT')

            <div class="row mb-3">

                <div class="col-md-4">
                    <label>PO Number</label>
                    <input type="text" class="form-control" value="{{ $po->po_number }}" disabled>
                </div>

                <div class="col-md-4">
                    <label>Job Project</label>
                    <select name="project_id" id="projectSelect" class="form-control"></select>
                </div>

                <div class="col-md-4">
                    <label>Supplier</label>
                    <select name="supplier_id" id="supplierSelect" class="form-control"></select>
                </div>

                <div class="col-md-4">
                    <label>Contract No</label>
                    <input type="text" name="contract_no" class="form-control" value="{{ $po->contract_no }}" required>
                </div>

                <div class="col-md-4">
                    <label>Project</label>
                    <input type="text" name="job" class="form-control" value="{{ $po->project->name }}" required>
                </div>

                <div class="col-md-4">
                    <label>Purchase Request</label>
                    <select name="pr_id" id="prSelect" class="form-control"></select>
                </div>

                {{-- <div class="col-md-4">
                    <label>Order No</label>
                    <input type="text" name="order_no" class="form-control" value="{{ $po->order_no }}" required>
                </div> --}}


                <div class="col-md-4">
                    <label>PO Date</label>
                    <input type="text" name="po_date" id="po_date" class="form-control" value="{{ $po->po_date }}" placeholder="DD-MM-YY" required>
                </div>


            </div>

            <hr>
            <h6>Items</h6>

            <table class="table table-bordered" id="itemTable">
                <thead>
                    <tr>
                        <th width="35%">Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th width="5%">
                            {{-- <button type="button" class="btn btn-success btn-sm" id="addRow">+</button> --}}
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($po->items as $item)
                    <tr>
                        <td>
                            <select name="items[][product_id]" class="form-control productSelect" data-selected="{{ $item->product_id }}"></select>
                        </td>
                        <td><input type="number" name="items[][qty]" class="form-control qty" value="{{ $item->qty }}" step="any"></td>
                        <td><input type="number" name="items[][unit_price]" class="form-control price" value="{{ $item->unit_price }}" step="any"></td>
                        <td class="total">{{ number_format($item->total_price) }}</td>
                        <td><button type="button" class="btn btn-danger btn-sm removeRow">x</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="text-end">
                <p>Subtotal: <span id="subtotal">{{ number_format($po->subtotal) }}</span></p>
                <p>PPN (11%): <span id="tax">{{ number_format($po->tax) }}</span></p>
                <h5>Grand Total: <span id="grand_total">{{ number_format($po->grand_total) }}</span></h5>
            </div>

            @if ($po->status == 'open')
                <button class="btn btn-primary mt-3">Update</button>
            @endif

        </form>
    </div>
</div>

@endsection
@push('scripts')
<script>

let supplierChoices;

// ========================
// LOAD SUPPLIER (AJAX)
// ========================
function loadSuppliers(selectedId = null) {
    $.get('/ajax/suppliers', function (res) {

        const supplierChoices = new Choices('#supplierSelect', {
            shouldSort: false,
        });

        supplierChoices.clearChoices();

        supplierChoices.setChoices(
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
                label: s.name,
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

$(document).ready(function () {

    // load suppliers
    loadSuppliers({{ $po->supplier_id }});
    loadProjects({{ $po->project_id }});
    loadPurchaseRequests({{ $po->project_id }}, {{ $po->purchase_request_id }});

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

    flatpickr("#po_date", {
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

    let items = [];

    $("#itemTable tbody tr").each(function () {
        let p = $(this).find(".productSelect").val();
        let q = $(this).find(".qty").val();
        let u = $(this).find(".price").val();

        if (p && q && u) {
            items.push({
                product_id: p,
                qty: q,
                unit_price: u
            });
        }
    });

    let data = {
        _token: "{{ csrf_token() }}",
        _method: "PUT",

        // project_name: $("input[name='project_name']").val(),
        contract_no: $("input[name='contract_no']").val(),
        job: $("input[name='job']").val(),
        // order_no: $("input[name='order_no']").val(),
        // pr_no: $("input[name='pr_no']").val(),
        po_date: $("input[name='po_date']").val(),
        supplier_id: $("#supplierSelect").val(),
        project_id: $("#projectSelect").val(),
        pr_id: $("#prSelect").val(),
        items: items
    };

    $.ajax({
        url: "/purchase-orders/" + id,
        method: "POST",
        data: data,
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
