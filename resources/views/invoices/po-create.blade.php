@extends('layouts.app')

@section('title', 'Create Invoice')

@push('styles')
@endpush

@section('content')

    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5>Create Invoice - PO {{ $po->po_number }}</h5>
            <a href="{{ route('purchase_orders.index') }}" class="btn btn-secondary">Back</a>
        </div>


        <div class="card-body">

            {{-- <div class="row mb-2">
            @foreach ($po->invoices as $item)
            <div class="col-md-3">
                <a class="btn btn-primary col-md-12" href="/invoices/{{ $item->id }}/print">{{ $item->invoice_number }}</a>
            </div>
            @endforeach
        </div> --}}

            <form id="editForm" data-id="{{ $po->id }}">

                @csrf
                @method('PUT')

                <div class="row mb-3">

                    <div class="col-md-4">
                        <label>PO Number</label>
                        <input type="text" class="form-control" value="{{ $po->po_number }}" disabled>
                        <input type="hidden" class="form-control" name="po_id" value="{{ $po->id }}" disabled>
                    </div>

                    <div class="col-md-4">
                        <label>Job Project</label>
                        <select name="project_id" id="projectSelect" class="form-control" disabled></select>
                    </div>

                    <div class="col-md-4">
                        <label>Supplier</label>
                        <select name="supplier_id" id="supplierSelect" class="form-control" disabled></select>
                    </div>

                    <div class="col-md-4">
                        <label>Contract No</label>
                        <input type="text" name="contract_no" class="form-control" value="{{ $po->contract_no }}"
                            disabled>
                    </div>

                    <div class="col-md-4">
                        <label>Project</label>
                        <input type="text" name="job" class="form-control" value="{{ $po->project->name }}" disabled>
                    </div>

                    <div class="col-md-4">
                        <label>Purchase Request</label>
                        <select name="pr_id" id="prSelect" class="form-control" disabled></select>
                    </div>

                    {{-- <div class="col-md-4">
                    <label>Order No</label>
                    <input type="text" name="order_no" class="form-control" value="{{ $po->order_no }}" required>
                </div> --}}


                    <div class="col-md-4">
                        <label>PO Date</label>
                        <input type="text" name="po_date" id="po_date" class="form-control" value="{{ $po->po_date }}"
                            placeholder="DD-MM-YY" disabled>
                    </div>

                    <div class="col-md-4">
                        <label>Invoice Number</label>
                        <input type="text" name="inv_number" class="form-control" value="" required>
                    </div>

                    <div class="col-md-4">
                        <label>Invoice Date</label>
                        <input type="text" name="inv_date" id="inv_date" class="form-control" value=""
                            placeholder="DD-MM-YY" required>
                    </div>

                    <div class="col-md-4">
                        <label>Total</label>
                        <input type="text" name="total" class="form-control" value="{{ $po->grand_total }}" required
                            id="grandTotal">
                    </div>
                    {{-- <div class="col-md-4">
                    <label>Sisa</label>
                    <input type="text" name="sisa" class="form-control" value="{{ $po->grand_total - $po->invoices->sum('grand_total') }}" required>
                </div> --}}

                    <div class="col-md-4">
                        <label>lampiran</label>
                        <input type="file" name="lampiran" id="lampiran" class="form-control">
                    </div>


                </div>

                <hr>
                <h6>Items</h6>

                <table class="table table-bordered" id="itemTable">
                    <thead>
                        <tr>
                            <th width="35%">Product</th>
                            <th>Qty PO</th>
                            <th>Qty Inv</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            {{-- <th width="5%"> --}}
                            {{-- <button type="button" class="btn btn-success btn-sm" id="addRow">+</button> --}}
                            {{-- </th> --}}
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($po->items as $item)
                            <tr>
                                <td>
                                    <input type="text" class="form-control" value="{{ $item->product->name }}" readonly>
                                    <input type="hidden" class="form-control po_item_id" value="{{ $item->id }}"
                                        readonly>
                                </td>
                                <td><input type="number" name="items[][qty]" class="form-control qty"
                                        value="{{ $item->qty - $item->qty_invoiced }}" step="any" readonly></td>
                                <td><input type="number" name="items[][qty_inv]" class="form-control qty_inv"
                                        max="{{ $item->qty - $item->qty_invoiced }}" step="any" required></td>
                                <td><input type="number" name="items[][unit_price]" class="form-control price"
                                        value="{{ $item->unit_price }}" step="any" readonly></td>
                                <td class="total">{{ number_format($item->total_price) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="text-end">
                    <p>Subtotal: <span id="subtotal">{{ number_format($po->subtotal) }}</span></p>
                    <p>PPN (11%): <span id="tax">{{ number_format($po->tax) }}</span></p>
                    <h5>Grand Total: <span id="grand_total">{{ number_format($po->grand_total) }}</span></h5>
                </div>

                @if ($po->status != 'closed')
                    <button class="btn btn-primary mt-3">Create Invoice</button>
                @endif

            </form>

        </div>
    </div>

    <div class="card mt-2">
        <div class="card-header">
            <h3>INVOICE</h3>
            <a href="#" class="btn btn-success mt-3 simpan-payment">Simpan Pembayaran</a>
        </div>
        <div class="card-body">
            <div class="list-group">
                <table class="table table-bordered inv-table">
                    <thead>
                        <tr>
                            <th width="15%">No Invoice</th>
                            <th>Tanggal Invoice</th>
                            <th width="40%">QTY INV</th>
                            <th>Nominal</th>
                            {{-- <th>Pembayaran</th> --}}
                            <th>Lampiran</th>
                        </tr>
                    </thead>
                    {{-- <tbody>
                    @foreach ($po->invoices as $item)
                    <tr>
                        <td><a href="/invoices/{{ $item->id }}">{{ $item->invoice_number }}</a></td>
                        <td>{{ $item->invoice_date }}</td>
                        <td>{{ $item->grand_total }}</td>
                        <td>
                            @if ($item->lampiran)
                                <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank">
                                    {{ $item->invoice_number }}.pdf
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody> --}}
                    <tbody>
                        @foreach ($po->invoices as $item)
                            {{-- @php
                    // dd($item->items)
                @endphp --}}
                            {{-- 🔷 ROW INVOICE --}}
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary toggle-sub"
                                        data-item="{{ $item->id }}">
                                        +
                                    </button>
                                    {{-- <a href="/invoices/{{ $item->id }}"> --}}
                                    {{ $item->invoice_number }}
                                    {{-- </a> --}}
                                </td>
                                <td>{{ $item->invoice_date }}</td>
                                <td>
                                    @foreach ($item->items as $invItem)
                                        <div class="inv-item row">
                                            <div class="col-md-4">
                                                <input type="text" class="form-control"
                                                    value="{{ $invItem->sourceItem->product->name }}" readonly>
                                            </div>
                                            <div class="col-md-4">
                                                <input type="number" class="form-control invItem_qty"
                                                    value="{{ $invItem->qty }}" step="any">
                                            </div>
                                        </div>
                                    @endforeach
                                </td>
                                <td>
                                    <input type="text" class="form-control gt_inv"
                                        value="{{ number_format($item->grand_total, 3) }}">
                                    <input type="hidden" class="form-control id_inv" value="{{ $item->id }}">
                                </td>
                                <td>
                                    @if ($item->lampiran)
                                        <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank">
                                            {{ $item->invoice_number }}.pdf
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            {{-- pembayaran --}}
                            <tr class="sub-row d-none" data-item="{{ $item->id }}">
                                <td colspan="3">
                                    <table class="table table-sm pembayaran-table" data-item="{{ $item->id }}">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Jumlah</th>
                                                <th>
                                                    <button type="button" class="btn btn-sm btn-primary add-pembayaran"
                                                        data-item="{{ $item->id }}">
                                                        + Tambah Pembayaran
                                                    </button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($item->poPayments as $sub)
                                                <tr>
                                                    <td>
                                                        <input type="hidden" class="form-control sub_id"
                                                            value="{{ $sub['id'] }}">
                                                        <input type="text" class="form-control sub_tanggal"
                                                            value="{{ $sub['sub_tanggal'] }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control sub_pembayaran"
                                                            value="{{ $sub['sub_pembayaran'] }}">
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
                                                        data-item="{{ $item->id }}" readonly>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </td>
                            </tr>
                        @endforeach

                        {{-- 🔸 SUB ROW PEMBAYARAN --}}
                        {{-- @foreach ($item->payments as $pay)
                        <tr style="background-color:#f9f9f9;">
                            <td></td>
                            <td>
                                <small>📅 {{ $pay->tanggal }}</small>
                            </td>
                            <td>
                                <small>💰 {{ number_format($pay->nominal, 0, ',', '.') }}</small>
                            </td>
                        </tr>
                    @endforeach --}}

                        {{-- @endforeach --}}
                    </tbody>
                </table>


                {{-- <div class="col-md-4 d-flex nowrap">
                <a href="/invoices/{{ $item->id }}/print" class="mt-2 list-group-item list-group-item-action">{{ $item->invoice_number }}</a>

                <p class="mt-2 list-group-item list-group-item-action" style="margin-left: 10px">{{ $item->grand_total }}</p>
            </div> --}}
            </div>
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
            $.get('/ajax/suppliers', function(res) {

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
            $.get('/ajax/projects', function(res) {

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
            // console.log(project_id);
            // console.log(selectedId);
            $.get('/ajax/prs', {
                project_id: project_id
            }, function(res) {

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

            $.get('/ajax/products', function(res) {

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

            $.get(`/ajax/prs/${prId}/items`, function(items) {

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

        $(document).ready(function() {

            // load suppliers
            loadSuppliers({{ $po->supplier_id }});
            loadProjects({{ $po->project_id }});
            loadPurchaseRequests({{ $po->project_id }}, {{ $po->purchase_request_id }});

            // load products for existing rows
            $(".productSelect").each(function() {
                let selected = $(this).data('selected');
                loadProducts(this, selected);
            });

            $('#prSelect').on('change', function() {
                let prId = $(this).val();
                if (!prId) return;

                loadPrItems(prId);
            });

            flatpickr("#po_date", {
                dateFormat: "Y-m-d", // format yg dikirim ke backend
                altInput: true, // tampilkan format ramah user
                altFormat: "d-m-Y", // contoh: 14 September 2025
                allowInput: true
            });

            flatpickr("#inv_date", {
                dateFormat: "Y-m-d", // format yg dikirim ke backend
                altInput: true, // tampilkan format ramah user
                altFormat: "d-m-Y", // contoh: 14 September 2025
                allowInput: true
            });

            // add new row
            $("#addRow").click(function() {
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
            $(document).on("click", ".removeRow", function() {
                $(this).closest("tr").remove();
                calculate();
            });

            // recalc
            $(document).on("input", ".qty_inv, .price", calculate);

            calculate();

        });

        // ======================
        // CALCULATE TOTAL
        // ======================
        function calculate() {
            let subtotal = 0;

            $("#itemTable tbody tr").each(function() {

                let qty = parseFloat($(this).find(".qty_inv").val());
                if (isNaN(qty)) {
                    qty = parseFloat($(this).find(".qty").val());
                }
                // console.log($(this).find(".qty").val());

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

        $(document).on('click', '.toggle-sub', function() {
            let item = $(this).data('item');

            let subRow = $(`.sub-row[data-item="${item}"]`);

            subRow.toggleClass('d-none');

            // ganti icon + / -
            if (subRow.hasClass('d-none')) {
                $(this).text('+');
            } else {
                $(this).text('-');
            }
        });

        $(document).on('input', '.sub_pembayaran', function() {
            let table = $(this).closest('.pembayaran-table');
            // let field = table.data('field');
            let item = table.data('item');

            hitungSisa(item);
        });

        $(document).on('click', '.add-pembayaran', function() {
            let field = $(this).data('field');
            let item = $(this).data('item');

            let table = $(`.pembayaran-table[data-item="${item}"] tbody`);

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
            <button type="button" class="btn btn-danger btn-sm remove-row">x</button>
        </td>
    </tr>
    `;

            table.append(row);

            flatpickr(".sub_tanggal", {
                dateFormat: "Y-m-d", // format yg dikirim ke backend
                altInput: true, // tampilkan format ramah user
                altFormat: "d-m-Y", // contoh: 14 September 2025
                allowInput: true
            });

        });

        $(document).ready(function() {

            $('.pembayaran-table').each(function() {
                let item = $(this).data('item');

                hitungSisa(item);
            });

        });

        function hitungSisa(item) {
            let total = parseNumber(
                $(`#grandTotal`).val() || 0
            );

            // console.log(total);


            let sum = 0;

            $(`.pembayaran-table[data-item="${item}"] .sub_pembayaran`).each(function() {
                let val = parseFloat($(this).val()) || 0;

                sum += val;
            });

            let sisa = total - sum;

            $(`.sisa-field[data-item="${item}"]`).val(sisa.toLocaleString());
        }

        $('.simpan-payment').on('click', function() {
            let id = $("#editForm").data('id');
            let item = $(this).data('item');

            let table = $(`.pembayaran-table[data-item="${item}"] tbody`);

            let pembayaran = [];
            let inv = [];

            // table.find('tr').each(function () {
            //     let tanggal = $(this).find('.sub_tanggal').val();
            //     let pembayaran = $(this).find('.sub_pembayaran').val();

            //     pembayaran.push({
            //         tanggal: tanggal,
            //         pembayaran: pembayaran
            //     });
            // });

            $('.pembayaran-table').each(function() {
                // let field = $(this).data('field');
                let item = $(this).data('item');

                $(this).find('tbody tr').each(function() {
                    pembayaran.push({
                        sub_id: $(this).find('.sub_id').val(),
                        item_id: item,
                        // field: field,
                        sub_tanggal: $(this).find('.sub_tanggal').val(),
                        sub_pembayaran: $(this).find('.sub_pembayaran').val(),
                        // sub_invoice: $(this).find('.sub_invoice').val()
                    });
                });
            });

            $('.inv-table').each(function() {
                // let field = $(this).data('field');
                // let item = $(this).data('item');

                $(this).find('tbody tr').each(function() {
                    inv.push({
                        id_inv: $(this).find(`.id_inv`).val(),
                        gt_inv: $(this).find(`.gt_inv`).val(),
                    });
                });
            });

            // console.log(pembayaran);


            $.ajax({
                url: "/invoices/po/",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    purchase_order_item_id: item,
                    pembayaran: pembayaran,
                    inv: inv,
                },
                success: function(res) {
                    toastSuccess(res.message);
                },
                error: function(xhr) {
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


        function parseNumber(value) {
            if (!value) return 0;

            // hapus koma (ribuan)
            value = value.replace(/,/g, '');

            return parseFloat(value) || 0;
        }

        // =====================
        // AJAX SUBMIT UPDATE
        // =====================
        $("#editForm").submit(function(e) {
            e.preventDefault();

            let id = $(this).data('id');

            let formData = new FormData();

            formData.append('_token', "{{ csrf_token() }}");
            formData.append('_method', "POST"); // kalau mau PUT ganti "PUT"

            formData.append('contract_no', $("input[name='contract_no']").val());
            formData.append('job', $("input[name='job']").val());
            formData.append('po_date', $("input[name='po_date']").val());
            formData.append('inv_date', $("input[name='inv_date']").val());
            formData.append('inv_number', $("input[name='inv_number']").val());
            formData.append('po_id', $("input[name='po_id']").val());
            formData.append('supplier_id', $("#supplierSelect").val());
            formData.append('project_id', $("#projectSelect").val());
            formData.append('pr_id', $("#prSelect").val());
            formData.append('type', 'PurchaseOrder');

            // =========================
            // ITEMS
            // =========================
            let items = [];

            $("#itemTable tbody tr").each(function() {
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

            // =========================
            // FILE (1 file)
            // =========================
            let fileInput = $('#lampiran')[0];

            if (fileInput.files.length > 0) {
                formData.append('lampiran', fileInput.files[0]);
            }

            // =========================
            // AJAX
            // =========================
            $.ajax({
                url: "{{ route('invoices.store') }}",
                method: "POST",
                data: formData,
                processData: false, // ❗ WAJIB
                contentType: false, // ❗ WAJIB
                success: function(res) {
                    toastSuccess(res.message);

                    setTimeout(() => {
                        window.location.href = res.redirect;
                    }, 800);
                },
                error: function(xhr) {
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
