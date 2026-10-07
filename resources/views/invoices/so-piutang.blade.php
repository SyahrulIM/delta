@extends('layouts.app')

@section('title', 'Create Invoice')

@push('styles')
@endpush

@section('content')

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Pembayaran Piutang - Invoice {{ $inv->invoice_number }}</h5>
            @if ($inv->status == 'closed')
                <span class="badge bg-success">Lunas</span>
            @elseif ($inv->status == 'partial')
                <span class="badge bg-warning">Dibayar Sebagian</span>
            @else
                <span class="badge bg-secondary">Belum Dibayar</span>
            @endif
        </div>
        <a href="{{ route('sales_orders.index') }}" class="btn btn-secondary">Back</a>
    </div>


    <div class="card-body">

        <form id="editForm" data-id="{{ $inv->id }}">

            @csrf
            @method('POST')

            <div class="row mb-3">

                <div class="col-md-4">
                    <label>Invoice Number</label>
                    <input type="text" name="inv_number" class="form-control" value="{{ $inv->invoice_number }}" readonly>
                </div>

                <div class="col-md-4">
                    <label>Invoice Date</label>
                    <input type="text" name="inv_date" id="inv_date" class="form-control" value="{{ $inv->invoice_date }}" placeholder="DD-MM-YY" required readonly>
                </div>
                <div class="col-md-4">
                    <label>No. Faktur</label>
                    <input type="text" name="no_faktur" id="no_faktur" class="form-control" value="{{ $inv->no_faktur }}" placeholder="No. Faktur" required readonly>
                </div>

                <div class="col-md-4">
                    <label>Total Piutang (Tagihan)</label>
                    <input type="text" class="form-control total-piutang" value="{{ $inv->grand_total }}" readonly>
                </div>

                <div class="col-md-4">
                    <label>Total Sudah Dibayar</label>
                    <input type="text" class="form-control total-bayar" value="{{ $totalBayar ?? $inv->piutangs->sum('amount') }}" readonly>
                </div>

                <div class="col-md-4">
                    <label>Sisa Piutang</label>
                    <input type="text" class="form-control sisa-piutang" value="{{ $sisaPiutang ?? max(0, $inv->grand_total - $inv->piutangs->sum('amount')) }}" readonly>
                </div>

                <div class="col-md-4">
                    <label>TOP</label>
                    <input type="text" class="form-control top" value="{{ $inv->top }}" readonly>
                </div>

                <div class="col-md-4">
                    <label>Jatuh Tempo</label>
                    <input type="text" name="jatuh_tempo" id="jatuh_tempo" class="form-control" value="{{ $inv->jatuh_tempo }}" placeholder="DD-MM-YY" readonly>
                </div>

            </div>

            <hr>
            <h6>Items</h6>

            <table class="table table-bordered" id="itemTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Nominal</th>
                        <th>Bank</th>
                        <th>Keterangan</th>
                        <th width="5%">
                            <button type="button" class="btn btn-success btn-sm" id="addRow">+</button>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($inv->piutangs as $item)
                    <tr>
                        <td>
                            <input type="text" class="form-control piutang_date" value="{{ $item->date }}" readonly>
                        </td>
                        <td><input type="number" class="form-control amount" value="{{ $item->amount }}" readonly></td>
                        <td><input type="text" class="form-control bank" value="{{ $item->bank }}" readonly></td>
                        <td><input type="text" class="form-control notes" value="{{ $item->note }}" readonly></td>
                        {{-- <td></td> --}}
                    </tr>
                    @endforeach
                </tbody>
            </table>


            @if ($inv->status != 'closed')
                <button type="submit" class="btn btn-primary mt-3">Simpan Pembayaran</button>
            @else
                <div class="alert alert-success mt-3"><i class="bi bi-check-circle"></i> Invoice ini telah lunas.</div>
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
            <input type="text" name="items[][piutang_date] class="form-control piutang_date" value="">
        </td>
        <td><input type="number" name="items[][amount]" class="form-control amount" value=""></td>
        <td><input type="text" name="items[][bank]" class="form-control bank" value=""></td>
        <td><input type="text" name="items[][notes]" class="form-control notes"></td>
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

    // load customers
    // loadCustomers({{ $inv->customer_id }});
    // loadProjects({{ $inv->project_id }});
    // loadPurchaseRequests({{ $inv->project_id }}, {{ $inv->purchase_request_id }});

    // load products for existing rows
    $(".productSelect").each(function () {
        let selected = $(this).data('selected');
        loadProducts(this, selected);
    });

    calculate();

    $('#prSelect').on('change', function () {
        let prId = $(this).val();
        if (!prId) return;

        loadPrItems(prId);
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
            <td>
                <input type="text" name="items[][piutang_date]" class="form-control piutang_date" value="">
            </td>
            <td><input type="number" name="items[][amount]" class="form-control amount" value=""></td>
            <td><input type="text" name="items[][bank]" class="form-control bank" value=""></td>
            <td><input type="text" name="items[][notes]" class="form-control notes"></td>
        </tr>`;
        $("#itemTable tbody").append(tr);

        flatpickr(".piutang_date", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
            allowInput: true
        });

        calculate();
    });

    // remove row
    $(document).on("click", ".removeRow", function () {
        $(this).closest("tr").remove();
        calculate();
    });

    // recalc
    $(document).on("input", ".amount", calculate);

});

// ======================
// CALCULATE TOTAL
// ======================
function calculate() {
    let totalInvoice = parseFloat($('.total-piutang').val()) || 0;
    let subtotal = 0;

    $("#itemTable tbody tr").each(function () {
        let amount = parseFloat($(this).find(".amount").val()) || 0;
        subtotal += amount;
    });

    let sisa = Math.max(0, totalInvoice - subtotal);

    $(".total-bayar").val(subtotal.toLocaleString('id-ID'));
    $(".sisa-piutang").val(sisa.toLocaleString('id-ID'));
}

// =====================
// AJAX SUBMIT UPDATE
// =====================
$("#editForm").submit(function (e) {
    e.preventDefault();

    let id = $(this).data('id');

    let items = [];

    $("#itemTable tbody tr").each(function () {
        let q = $(this).find('[name="items[][piutang_date]"]').val();
        let qi = $(this).find('[name="items[][amount]"]').val();
        let poi = $(this).find('[name="items[][bank]"]').val();
        let u = $(this).find('[name="items[][notes]"]').val();

        if (q && qi && parseFloat(qi) > 0) {
            items.push({
                piutang_date: q,
                amount: parseFloat(qi),
                bank: poi || '',
                notes: u || ''
            });
        }
    });

    if (items.length === 0) {
        toastError("Silakan isi tanggal dan nominal pembayaran terlebih dahulu pada baris baru.", "warning");
        return;
    }

    let data = {
        _token: "{{ csrf_token() }}",
        id: id,
        inv_date: $("input[name='inv_date']").val(),
        inv_number: $("input[name='inv_number']").val(),
        type: 'SalesOrder',
        items: items
    };

    $.ajax({
        url: "{{ route('invoices.piutangStore') }}",
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
                let msg = xhr.responseJSON.message || Object.values(xhr.responseJSON.errors || {})[0]?.[0] || "Validasi gagal";
                toastError(msg, "error");
            } else {
                toastError("Server error", "error");
            }
        }
    });
});
</script>
@endpush
