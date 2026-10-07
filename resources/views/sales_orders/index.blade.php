@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Sales Orders</h5>
        @if (auth()->user()->role == 'admin')
        <a href="{{ route('sales_orders.export') }}" class="btn btn-success">
            Export Excel
        </a>
        @endif
        <a href="{{ route('sales_orders.create') }}" class="btn btn-primary">+ Create</a>
    </div>

    <div class="card-body">
        <table class="table table-bordered" id="soTable">
            <thead>
                <tr>
                    <th>JOB</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th width="150">Action</th>
                </tr>
            </thead>
        </table>

        <h3>TOTAL :</h3>
        <h3 id="totalGrand"></h3>
    </div>

</div>
@endsection

@push('scripts')
<script src="{{ asset('mazer/assets/extensions/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('mazer/assets/extensions/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('mazer/assets/extensions/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('mazer/assets/extensions/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
$(function() {

    let table = $('#soTable').DataTable({
        ajax: "{{ route('sales_orders.data') }}",
        columns: [
            { data: 'project.job' },
            { data: 'so_date' },
            {
                width: '25%',
                data: 'customer_name',
                render: function(data){
                    return data ?? '-';
                }
            },
            {
                data: 'grand_total',
                render: function(data){
                    return parseFloat(data.replace(',', '.')).toLocaleString('en-US');
                }
            },
            {
                data: 'status',
                className: 'text-center',
                render: function(data){
                    return `<span class="badge bg-secondary">${data}</span>`;
                }
            },
            {
            data: 'id',
            className: 'text-center',
            render: function (id, type, row) {
                return `
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item text-warning" href="/sales-orders/${id}/edit">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-primary" href="/sales-orders/so-invoice/${id}">
                                    <i class="bi bi-file-earmark-text"></i> Invoice
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-info" href="/sales-orders/so-margin/${id}">
                                    <i class="bi bi-graph-up"></i> Margin
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-success" href="/sales-orders/so-excel/${id}">
                                    <i class="bi bi-file-earmark-spreadsheet"></i> Export Excel
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item text-danger btn-delete" data-id="${id}">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                `;
            },
            orderable: false,
            searchable: false
        }
        ],
         // 🔥 INI KUNCINYA
        drawCallback: function(settings) {
            let api = this.api();

            let total = api
                .column(3, { search: 'applied' }) // kolom grand_total
                .data()
                .reduce(function (a, b) {
                    return parseFloat(a) + parseFloat(b);
                }, 0);

            $('#totalGrand').text('Rp.' + total.toLocaleString('id-ID'));
        }
    });

    // DELETE
    $(document).on('click', '.btn-delete', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: "Hapus SO?",
            text: "Data tidak bisa dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Ya, hapus!"
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: `/sales-orders/${id}`,
                    method: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function () {
                        Swal.fire("Deleted!", "SO berhasil dihapus.", "success");
                        table.ajax.reload();
                    },
                    error: function () {
                        Swal.fire("Error!", "Gagal menghapus SO", "error");
                    }
                });

            }
        });
    });

});
</script>
@endpush
