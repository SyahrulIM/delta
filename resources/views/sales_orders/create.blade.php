@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Create Sales Order</h5>
        <a href="{{ route('sales_orders.index') }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="card-body">

        <form action="{{ route('sales_orders.store') }}" method="POST">
            @csrf

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>SO Number</label>
                    <input type="text" name="so_number" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>Job Project</label>
                    <select name="project_id" id="projectSelect" class="form-control" required>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Contract No.</label>
                    <input type="text" name="contract_no" class="form-control" readonly required>
                </div>
                <div class="col-md-4">
                    <label>Project</label>
                    <input type="text" name="job" class="form-control" readonly required>
                </div>
                {{-- <div class="col-md-4">
                    <label>Invoice No.</label>
                    <input type="text" name="invoice_no" class="form-control" required>
                </div> --}}

                <div class="col-md-4">
                    <label>SO Date</label>
                    <input type="date" name="so_date" id="so_date" class="form-control" placeholder="DD-MM-YYYY" required>
                </div>

                <div class="col-md-4">
                    <label>Company Name</label>
                    <input type="text" name="customer_name" id="customer_name" class="form-control" readonly>
                </div>
            </div>

            <hr>

            <h6>Items</h6>
            <table class="table table-bordered" id="itemTable">
                <thead>
                    <tr>
                        <th style="width: 35%">Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th width="5%"><button type="button" id="addRow" class="btn btn-success btn-sm">+</button></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <hr>

            <div class="text-end">
                <p>Subtotal: <span id="subtotal">0</span></p>
                <p>PPN (11%): <span id="tax">0</span></p>
                <h5>Grand Total: <span id="grand_total">0</span></h5>
            </div>

            <button class="btn btn-primary mt-3">Save</button>

        </form>
    </div>
</div>
@endsection

@push('scripts')

<script>
    let customerChoices;
let productChoices = [];

$(document).ready(function () {
    // loadCustomers();
    loadProjects();

    // First row auto-add
    addRow();

    flatpickr("#so_date", {
        dateFormat: "Y-m-d",      // format yg dikirim ke backend
        altInput: true,           // tampilkan format ramah user
        altFormat: "d-m-Y",       // contoh: 14 September 2025
        allowInput: true
    });

    // $('#projectSelect').on('change', function () {
    //     loadPurchaseRequests(this.value);
    // });
});

// =============================
//  LOAD CUSTOMERS (AJAX)
// =============================
function loadCustomers() {
    $.get('/ajax/customers', function (res) {

        customerChoices = new Choices('#customerSelect', {
            placeholderValue: 'Select Customer...',
            searchPlaceholderValue: 'Search customer...',
        });

        let list = res.map(r => ({ value: r.id, label: r.name }));

        customerChoices.setChoices(list, 'value', 'label', true);

    });
}


function loadProjects() {
    $.get('/ajax/projects', function (res) {

        projectChoices = new Choices('#projectSelect', {
            placeholderValue: 'Select Project...',
            searchPlaceholderValue: 'Search project...',
        });

        let list = res.map(r => ({
            value: r.id,
            label: r.job,
            customProperties: {
                job: r.job,
                contract_no: r.contract_no,
                customer_name: r.customer_name,
                project: r.name
            }
        }));

        projectChoices.setChoices(list, 'value', 'label', true);

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

// ============================
//  LOAD PRODUCTS (AJAX)
// ============================
function loadProducts(selectElement) {

    let choice = new Choices(selectElement, {
        placeholderValue: 'Select product...',
        searchPlaceholderValue: 'Search product...',
    });

    productChoices.push(choice);

    $.get('/ajax/products', function (res) {
        let list = res.map(r => ({ value: r.id, label: r.name }));
        choice.setChoices(list, 'value', 'label', true);
    });
}

// ============================
//  ADD ROW ITEM
// ============================
function addRow() {
    let row = `
    <tr>
        <td>
            <select name="items[][product_id]" class="form-control productSelect"></select>
        </td>
        <td><input type="number" name="items[][qty]" class="form-control qty" value="0" step="any"></td>
        <td><input type="number" name="items[][unit_price]" class="form-control price" value="0" step="any"></td>
        <td class="total">0</td>
        <td>
            <button type="button" class="btn btn-danger btn-sm removeRow">x</button>
        </td>
    </tr>
    `;

    $("#itemTable tbody").append(row);

    let latestSelect = $("#itemTable tbody tr:last .productSelect")[0];
    loadProducts(latestSelect);

    calculate();
}

$("#addRow").click(addRow);

$(document).on("click", ".removeRow", function(){
    $(this).closest("tr").remove();
    calculate();
});

$(document).on("input", ".qty, .price", function(){
    calculate();
});

// ============================
//  CALCULATE TOTALS
// ============================
function calculate() {
    let subtotal = 0;

    $("#itemTable tbody tr").each(function(){
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

// ============================
//  AJAX SUBMIT FORM
// ============================
$("form").submit(function (e) {
    e.preventDefault();

    let items = [];

    $("#itemTable tbody tr").each(function () {
        let product = $(this).find(".productSelect").val();
        let qty     = $(this).find(".qty").val();
        let price   = $(this).find(".price").val();

        // HANYA PUSH ROW YANG VALID
        if (product && qty && price) {
            items.push({
                product_id: product,
                qty: qty,
                unit_price: price
            });
        }
    });

    if (items.length === 0) {
        toast("Item tidak boleh kosong", "error");
        return;
    }

    let data = {
        so_number: $("input[name='so_number']").val(),
        so_date: $("input[name='so_date']").val(),
        project_id: $("#projectSelect").val(),
        contract_no: $("input[name='contract_no']").val(),
        job: $("input[name='job']").val(),
        invoice_no: $("input[name='invoice_no']").val(),
        // pr_no: $("input[name='pr_no']").val(),
        customer_name: $("#customer_name").val(),
        items: items,
        _token: "{{ csrf_token() }}"
    };

    $.ajax({
        url: "{{ route('sales_orders.store') }}",
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
                const errors = xhr.responseJSON.errors;
                const msg = Object.values(errors)[0][0];
                toastError(msg, "error");
            } else {
                toastError("Server error", "error");
            }
        }
    });
});

</script>
@endpush
