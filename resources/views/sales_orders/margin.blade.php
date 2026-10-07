@extends('layouts.app')

@section('title', 'Margin Sales Order')

@push('styles')
@endpush

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Margin Sales Order</h5>
        <a href="{{ route('sales_orders.so-margin-export', $so->id) }}" class="btn btn-success btn-sm">
            Export
        </a>
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
                    <select name="project_id" id="projectSelect" class="form-control" disabled></select>
                </div>

                <div class="col-md-4">
                    <label>Contract No</label>
                    <input type="text" name="contract_no" class="form-control" value="{{ $so->contract_no }}" disabled>
                </div>

                <div class="col-md-4">
                    <label>Project</label>
                    <input type="text" name="job" class="form-control" value="{{ $so->project->name }}" disabled>
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
                    <input type="date" name="so_date" id="so_date" class="form-control" value="{{ $so->so_date }}"
                        disabled placeholder="DD-MM-YYYY">
                </div>

                {{-- <div class="col-md-4">
                    <label>Customer</label>
                    <select name="customer_id" id="customerSelect" class="form-control" disabled></select>
                </div> --}}
            </div>

            <hr>
            <h6>Items</h6>

            <table class="table table-bordered" id="itemTable">
                <thead>
                    <tr>
                        <th width="35%">Product</th>
                        <th width="15%">Qty</th>
                        <th width="20%">Unit Price</th>
                        <th width="30%">Total</th>
                    </tr>
                </thead>

                @foreach($so->items as $item)
                <tbody>
                    @php
                    $m = $item->margin;
                    // dump($m);
                    @endphp

                    {{-- ITEM HEADER --}}
                    <tr class="table-light">
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->qty }}</td>
                        <td>{{ number_format($item->unit_price) }}</td>
                        <td>{{ number_format($item->total_price) }}</td>
                    </tr>

                    {{-- MARGIN TITLE --}}
                    <tr class="table-secondary">
                        <th colspan="4">PERHITUNGAN LABA / RUGI</th>
                    </tr>

                    {{-- A. HARGA BELI --}}
                    <tr>
                        <th>A. Harga Beli</th>
                        <td colspan="1"></td>
                        <td>
                            <input type="number" class="form-control purchase_cost"
                                name="items[{{ $item->id }}][purchase_cost]" value="{{ $m->purchase_cost ?? 0 }}">
                        </td>
                    </tr>

                    {{-- B. BIAYA --}}
                    <tr class="table-secondary">
                        <th colspan="4">B. Biaya-Biaya (%)</th>
                    </tr>

                    @php
                    $fields = [
                        'contractor_percent' => 'Fee Kontraktor',
                        'expedition_percent' => 'Biaya Expedisi',
                        'test_percent' => 'Biaya Tes',
                        'marketing_percent' => 'Komisi Marketing',
                        'trip_percent' => 'Biaya Perjalanan',
                        'salary_percent' => 'Gaji',
                        'pph_percent' => 'PPH',
                        'scf_percent' => 'SCF',
                        'bunga_bank_percent' => 'Bunga Bank',
                        'retensi_percent' => 'Retensi',
                    ];
                    @endphp

                    @foreach($fields as $field => $label)
                    <tr>
                        <td>
                            <button type="button"
                                class="btn btn-sm btn-outline-primary toggle-sub"
                                data-field="{{ $field }}"
                                data-item="{{ $item->id }}">
                                +
                            </button>
                            {{ $loop->iteration }}. {{ $label }}
                        </td>
                        <td>
                            <div class="input-group mb-3">
                                <input type="number" step="0.01"
                                    class="form-control percent-field"
                                    data-field="{{ $field }}"
                                    data-item="{{ $item->id }}"
                                    name="items[{{ $item->id }}][{{ $field }}]"
                                    value="{{ $m->$field ?? 0 }}">
                                <span class="input-group-text">%</span>
                            </div>
                        </td>
                        <td>
                            <div class="input-group mb-3">
                                <span class="input-group-text">Rp.</span>
                                <input type="text" step="0.01"
                                    class="form-control rupiah-field total-biaya"
                                    data-field="{{ $field }}"
                                    data-item="{{ $item->id }}"
                                    value="{{ isset($m->$field) ? number_format($m->$field * $item->total_price / 100) : 0 }}"
                                    readonly>
                            </div>
                        </td>
                    </tr>

                    {{-- SUB PEMBAYARAN --}}
                    <tr class="sub-row d-none"
                        data-field="{{ $field }}"
                        data-item="{{ $item->id }}">
                        <td colspan="3">
                            <table class="table table-sm pembayaran-table" data-field="{{ $field }}" data-item="{{ $item->id }}">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jumlah</th>
                                        <th>No. Invoice</th>
                                        <th>
                                            <button type="button" class="btn btn-sm btn-primary add-pembayaran"
                                                data-field="{{ $field }}" data-item="{{ $item->id }}">
                                                + Tambah Pembayaran
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($subs->where('item_id', $item->id)->where('field', $field) as $sub)
                                        <tr>
                                            <td>
                                                <input type="hidden" class="form-control sub_id"
                                                    {{-- name="items[{{ $item->id }}][subs][{{ $field }}][][id]" --}}
                                                    value="{{ $sub['id'] }}">
                                                <input type="text"
                                                    class="form-control sub_tanggal"
                                                    {{-- name="items[{{ $item->id }}][subs][{{ $field }}][][tanggal]" --}}
                                                    value="{{ $sub['sub_tanggal'] }}">
                                            </td>
                                            <td>
                                                <input type="number"
                                                    class="form-control sub_pembayaran"
                                                    {{-- name="items[{{ $item->id }}][subs][{{ $field }}][][jumlah]" --}}
                                                    value="{{ $sub['sub_pembayaran'] }}">
                                            </td>
                                            <td>
                                                <input type="text"
                                                    class="form-control sub_invoice"
                                                    {{-- name="items[{{ $item->id }}][subs][{{ $field }}][][jumlah]" --}}
                                                    value="{{ $sub['sub_invoice'] }}">
                                            </td>
                                            <td>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td><strong>Sisa</strong></td>
                                        <td>
                                            <input type="text" class="form-control sisa-field"
                                                data-field="{{ $field }}"
                                                data-item="{{ $item->id }}"
                                                readonly>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </td>
                    </tr>
                    @endforeach

                    {{-- @foreach($fields as $field => $label)
                    <tr>
                        <td>{{ $loop->iteration }}. {{ $label }}</td>
                        <td>
                            <div class="input-group mb-3">
                                <input type="number" step="0.01" class="form-control percent-field"
                                    name="items[{{ $item->id }}][{{ $field }}]" value="{{ $m->$field ?? 0 }}">
                                <span class="input-group-text" id="basic-addon2">%</span>
                            </div>
                        </td>
                        <td>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon2">Rp.</span>
                                <input type="number" step="0.01" class="form-control rupiah-field" readonly
                                    name="rupiahs[{{ $item->id }}][{{ $field }}]" value="{{ isset($m->$field) ? ($m->$field * $item->total_price / 100) : 0 }}">
                            </div>
                        </td>
                    </tr>
                    @endforeach --}}

                    {{-- TOTAL BIAYA --}}
                    <tr class="table-warning">
                        <th>Total Biaya (%)</th>
                        {{-- <td colspan="2"></td> --}}
                        <td>
                            <div class="input-group mb-3">
                            <input type="number" readonly class="form-control total_percent"
                                value="{{ $m->total_percent ?? 0 }}">
                                <span class="input-group-text" id="basic-addon2">%</span>
                            </div>
                        </td>
                    </tr>

                    {{-- C. HARGA NETTO --}}
                    <tr>
                        <th>C. Harga Netto</th>
                        <td colspan="1"></td>
                        <td>
                            <input type="text" readonly class="form-control harga_netto"
                                value="{{ isset($m->harga_netto) ? number_format($m->harga_netto) : 0 }}">
                        </td>
                    </tr>
                    <tr>
                        <th></th>
                        <td colspan="1"></td>
                        <td>
                            <input type="text" readonly class="form-control total_netto"
                                value="{{ isset($m->total_netto) ? number_format($m->total_netto) : 0 }}">
                        </td>
                    </tr>

                    {{-- D. MARGIN --}}
                    <tr class="table-success">
                        <th>D. Margin</th>
                        <td colspan="1">
                            <div class="input-group mb-3">
                            <input type="number" readonly class="form-control margin_percent"
                                value="{{ $m->margin_percent ?? 0 }}">
                                <span class="input-group-text" id="basic-addon2">%</span>
                            </div>
                        </td>
                        <td colspan="1">
                            <input type="text" readonly class="form-control margin_value"
                                value="{{ isset($m->margin_value) ? number_format($m->margin_value) : 0 }}">
                        </td>
                    </tr>

                    {{-- SPACE --}}
                    <tr>
                        <td colspan="4">&nbsp;</td>
                    </tr>

                </tbody>
                    @endforeach
            </table>

            <div class="d-none">

                <h3>Biaya</h3>
                <p>{{ number_format($so->subtotal) }} * {{ $m->margin_percent ?? 0 }} % = {{ number_format($so->subtotal * ($m->margin_percent ?? 0) / 100) }}</p>

                <h3>Pembayaran</h3>
                <table class="table table-bordered" id="pembayaranTable">
                    <thead>
                        <tr>
                            <th style="width: 35%">Tanggal</th>
                            <th>Nominal</th>
                            <th width="5%"><button type="button" id="addRow" class="btn btn-success btn-sm">+</button></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pembayaran as $item)
                        <tr>
                            <td>
                                <input type="text" name="" class="form-control tanggal_pembayaran" value="{{ $item['tanggal_pembayaran'] }}" readonly>
                                <input type="hidden" name="" class="form-control pembayaran_id" value="{{ $item['id'] }}">
                            </td>
                            <td><input type="number" name="" class="form-control pembayaran" value="{{ $item['nominal'] }}" readonly></td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total Pembayaran</th>
                            <td colspan="">
                                <input type="text" readonly class="form-control total_pembayaran" value="{{ number_format($pembayaran->sum('nominal')) }}">
                            </td>
                        </tr>
                        <tr>
                            <th>Sisa Pembayaran</th>
                            <td colspan="">
                                <input type="text" readonly class="form-control total_pembayaran" value="{{ number_format($so->items[0]->total_price - $pembayaran->sum('nominal')) }}">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- <div class="text-end">
                <p>Subtotal: <span id="subtotal">{{ number_format($so->subtotal) }}</span></p>
                <p>PPN (11%): <span id="tax">{{ number_format($so->tax) }}</span></p>
                <h5>Grand Total: <span id="grand_total">{{ number_format($so->grand_total) }}</span></h5>
            </div> --}}

            <button class="btn btn-primary mt-3">Update</button>

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

function recalcRow(block) {

    let unitPrice = parseNumber(block.find('td:nth-child(3)').text().replace(/,/g,'')) || 0;

    let purchaseCost = parseNumber(block.find('.purchase_cost').val()) || 0;

    let totalPercent = 0;
    block.find('.percent-field').each(function () {
        totalPercent += parseNumber($(this).val()) || 0;
    });

    // Harga netto (biaya persen)
    let hargaNetto = unitPrice * (totalPercent / 100);

    // Total netto
    let totalNetto = purchaseCost + hargaNetto;

    // Margin
    let marginValue = unitPrice - totalNetto;
    let marginPercent = unitPrice > 0 ? (marginValue / unitPrice) * 100 : 0;

    // Set value
    block.find('.total_percent').val(totalPercent.toFixed(2));
    block.find('.harga_netto').val(hargaNetto.toFixed(2));
    block.find('.total_netto').val(totalNetto.toFixed(2));
    block.find('.margin_value').val(marginValue.toFixed(2));
    block.find('.margin_percent').val(marginPercent.toFixed(1));

    // Highlight margin negatif
    block.find('.margin_value')
        .toggleClass('is-invalid', marginValue < 0)
        .toggleClass('is-valid', marginValue >= 0);
}

// trigger event
$(document).on('input', '.percent-field, .purchase_cost', function () {

    let block = $(this).closest('tbody');

    // karena tiap item punya blok sendiri,
    // kita ambil parent tbody lalu filter item
    recalcRow($(this).closest('tbody'));
});

$(document).on('input', '.percent-field', function () {

    let tbody = $(this).closest('tbody');
    let totalPrice = parseFloat(tbody.find('td:nth-child(4)').text().replace(/,/g,'')) || 0;
    let value = $(this).val();

    let rupiah = $(this).parents('tr').find('.rupiah-field');

    rupiah.val(formatRupiah(totalPrice * (value / 100)));

});

function formatRupiah(angka) {
    return new Intl.NumberFormat('en-US').format(angka);
}

$(document).on('click', '.add-pembayaran', function () {
    let field = $(this).data('field');
    let item = $(this).data('item');

    let table = $(`.pembayaran-table[data-field="${field}"][data-item="${item}"] tbody`);

    let index = table.find('tr').length;

    let row = `
    <tr>
        <td>
            <input type="text" class="form-control sub_tanggal">
        </td>
        <td>
            <input type="number" class="form-control sub_pembayaran">
        </td>
        <td>
            <input type="text" class="form-control sub_invoice">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm remove-row">x</button>
        </td>
    </tr>
    `;

    table.append(row);

    flatpickr(".sub_tanggal", {
        dateFormat: "Y-m-d",      // format yg dikirim ke backend
        altInput: true,           // tampilkan format ramah user
        altFormat: "d-m-Y",       // contoh: 14 September 2025
        allowInput: true
    });

});

function hitungSisa(field, item) {
    let total = parseNumber(
        $(`.total-biaya[data-field="${field}"][data-item="${item}"]`).val() || 0
    );

console.log(total);

    let sum = 0;

    $(`.pembayaran-table[data-field="${field}"][data-item="${item}"] .sub_pembayaran`).each(function () {
        let val = parseFloat($(this).val()) || 0;

        sum += val;
    });

    let sisa = total - sum;

    $(`.sisa-field[data-field="${field}"][data-item="${item}"]`).val(sisa.toLocaleString());
}

function parseNumber(value) {
    if (!value) return 0;

    // hapus koma (ribuan)
    value = value.replace(/,/g, '');

    return parseFloat(value) || 0;
}

$(document).on('click', '.toggle-sub', function () {
    let field = $(this).data('field');
    let item = $(this).data('item');

    let subRow = $(`.sub-row[data-field="${field}"][data-item="${item}"]`);

    subRow.toggleClass('d-none');

    // ganti icon + / -
    if (subRow.hasClass('d-none')) {
        $(this).text('+');
    } else {
        $(this).text('-');
    }
});

$(document).on('input', '.sub_pembayaran', function () {
    let table = $(this).closest('.pembayaran-table');
    let field = table.data('field');
    let item = table.data('item');

    hitungSisa(field, item);
});

$(document).ready(function () {

    $('.pembayaran-table').each(function () {
        let field = $(this).data('field');
        let item  = $(this).data('item');

        hitungSisa(field, item);
    });

});

$(document).on('click', '.remove-row', function () {
    let table = $(this).closest('.pembayaran-table');
    let field = table.data('field');
    let item = table.data('item');

    $(this).closest('tr').remove();

    hitungSisa(field, item);
});

$(document).ready(function () {

    // $('#itemTable tbody').each(function () {
    //     recalcRow($(this));
    // });

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
            <td>
                <input type="text" name="pembayaran_items[tanggal_pembayaran][]" class="form-control tanggal_pembayaran" >
                <input type="hidden" name="pembayaran_items[pembayaran_id][]" class="form-control pembayaran_id">
            </td>
            <td><input type="number" name="pembayaran_items[pembayaran][]" class="form-control pembayaran" value="0"></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">x</button></td>
        </tr>`;
        $("#pembayaranTable tbody").append(tr);

        flatpickr(".tanggal_pembayaran", {
            dateFormat: "Y-m-d",      // format yg dikirim ke backend
            altInput: true,           // tampilkan format ramah user
            altFormat: "d-m-Y",       // contoh: 14 September 2025
            allowInput: true
        });

        // let last = $("#pembayaranTable tbody tr:last .productSelect")[0];
        // loadProducts(last);
        // calculate();
    });

    // remove row
    $(document).on("click", ".removeRow", function () {
        $(this).closest("tr").remove();
        // calculate();
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
        let qty = parseNumber($(this).find(".qty").val()) || 0;
        let price = parseNumber($(this).find(".price").val()) || 0;
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

    $("#pembayaranTable tbody tr").each(function () {
        let p = $(this).find(".tanggal_pembayaran").val();
        let q = $(this).find(".pembayaran_id").val();
        let u = $(this).find(".pembayaran").val();

        if (p && q && u) {
            items.push({
                tanggal_pembayaran: p,
                pembayaran_id: q,
                pembayaran: u
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
        customer_id: $("#customerSelect").val(),
        project_id: $("#projectSelect").val(),
        items: items
    };

    let formData = new FormData(this);

    let pembayaran = [];

    $('.pembayaran-table').each(function () {
        let field = $(this).data('field');
        let item = $(this).data('item');

        $(this).find('tbody tr').each(function () {
            pembayaran.push({
                sub_id: $(this).find('.sub_id').val(),
                item_id: item,
                field: field,
                sub_tanggal: $(this).find('.sub_tanggal').val(),
                sub_pembayaran: $(this).find('.sub_pembayaran').val(),
                sub_invoice: $(this).find('.sub_invoice').val()
            });
        });
    });

    formData.append('pembayaran_subs', JSON.stringify(pembayaran));

    $.ajax({
        url: "/sales-orders/margin/" + id,
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
