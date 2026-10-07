@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Purchase Orders</h5>
        <a href="{{ route('purchase_orders.export') }}" class="btn btn-success">
            Export Excel
        </a>
        <a href="{{ route('purchase_orders.create') }}" class="btn btn-primary">+ Create</a>
    </div>

    <div class="card-body">
        <table class="table table-bordered" id="poTable">
            <thead>
                <tr>
                    <th>JOB</th>
                    <th>PO Number</th>
                    {{-- <th>Date</th> --}}
                    <th>Supplier</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th width="150">Action</th>
                </tr>
            </thead>
        </table>
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

    let table = $('#poTable').DataTable({
        ajax: "{{ route('purchase_orders.data') }}",
        columns: [
            { data: 'project.job' },
            { data: 'po_number' },
            // { data: 'po_date' },
            {
                width: '25%',
                data: 'supplier',
                render: function(data){
                    return data?.name ?? '-';
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
            width: '15%',
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
                                <a class="dropdown-item text-warning" href="/purchase-orders/${id}/edit">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-primary" href="/purchase-orders/po-invoice/${id}">
                                    <i class="bi bi-file-earmark-text"></i> Invoice
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-success" href="/purchase-orders/po-excel/${id}">
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
        ]
    });

    // DELETE
    $(document).on('click', '.btn-delete', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: "Hapus PO?",
            text: "Data tidak bisa dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Ya, hapus!"
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: `/purchase-orders/${id}`,
                    method: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function () {
                        Swal.fire("Deleted!", "PO berhasil dihapus.", "success");
                        table.ajax.reload();
                    },
                    error: function () {
                        Swal.fire("Error!", "Gagal menghapus PO", "error");
                    }
                });

            }
        });
    });

});
</script>
@endpush
