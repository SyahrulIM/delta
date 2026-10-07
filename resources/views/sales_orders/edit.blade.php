@extends('layouts.app')

@section('title', 'Edit Sales Order')

@push('styles')
@endpush

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Edit Sales Order</h5>
        <a href="{{ route('sales_orders.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card-body">

        <form id="editForm" data-id="{{ $so->id }}">

            @csrf
            @method('PUT')

            <div class="row mb-3">

                <div class="col-md-4">
                    <label>SO Number</label>
                    <input type="text" class="form-control" value="{{ $so->so_number }}" disabled>
                </div>

                <div class="col-md-4">
                    <label>Job Project</label>
                    <select name="project_id" id="projectSelect" class="form-control"></select>
                </div>

                <div class="col-md-4">
                    <label>Contract No</label>
                    <input type="text" name="contract_no" class="form-control" value="{{ $so->contract_no }}" required>
                </div>

                <div class="col-md-4">
                    <label>Project</label>
                    <input type="text" name="job" class="form-control" value="{{ $so->project->name }}" required>
                </div>

                {{-- <div class="col-md-4">
                    <label>Invoice No</label>
                    <input type="text" name="invoice_no" class="form-control" value="{{ $so->invoice_no }}" required>
                </div> --}}

                {{-- <div class="col-md-4">
                    <label>PR No</label>
                    <input type="text" name="pr_no" class="form-control" value="{{ $so->pr_no }}" required>
                </div> --}}

                <div class="col-md-4">
                    <label>SO Date</label>
                    <input type="date" name="so_date" id="so_date" class="form-control" value="{{ $so->so_date }}" required placeholder="DD-MM-YYYY">
                </div>

                <div class="col-md-4">
                    <label>Company Name</label>
                    <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{ $so->customer_name }}" readonly>
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
                            <button type="button" class="btn btn-success btn-sm" id="addRow">+</button>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($so->items as $item)
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
                <p>Subtotal: <span id="subtotal">{{ number_format($so->subtotal) }}</span></p>
                <p>PPN (11%): <span id="tax">{{ number_format($so->tax) }}</span></p>
                <h5>Grand Total: <span id="grand_total">{{ number_format($so->grand_total) }}</span></h5>
            </div>

            @if ($so->status == 'open')
                <button class="btn btn-primary mt-3">Update</button>
            @endif

        </form>
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
                customProperties: {
                    job: s.job,
                    contract_no: s.contract_no,
                    customer_name: s.customer_name,
                    project: s.name
                },
                selected: selectedId ? s.id == selectedId : false
            })),
            'value',
            'label',
            true
        );

        // 🔥 EVENT: ketika project dipilih
        document.querySelector('#projectSelect')
        .addEventListener('change', function () {
            let selected = projectChoices.getValue(true);
            let choice = projectChoices._currentState.choices.find(c => c.value == selected);

            $("input[name='job']").val(choice?.customProperties?.project ?? '');
            $("input[name='contract_no']").val(choice?.customProperties?.contract_no ?? '');
            $("input[name='customer_name']").val(choice?.customProperties?.customer_name ?? '');
        });
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


$(document).ready(function () {

    // load customers
    // loadCustomers({{ $so->customer_id }});
    loadProjects({{ $so->project_id }});

    // load products for existing rows
    $(".productSelect").each(function () {
        let selected = $(this).data('selected');
        loadProducts(this, selected);
    });

    flatpickr("#so_date", {
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
            <td><input type="number" name="items[][qty]" class="form-control qty" value="0" step="any"></td>
            <td><input type="number" name="items[][unit_price]" class="form-control price" value="0" step="any"></td>
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
        order_no: $("input[name='order_no']").val(),
        invoice_no: $("input[name='invoice_no']").val(),
        pr_no: $("input[name='pr_no']").val(),
        so_date: $("input[name='so_date']").val(),
        customer_name: $("#customer_name").val(),
        project_id: $("#projectSelect").val(),
        items: items
    };

    $.ajax({
        url: "/sales-orders/" + id,
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
