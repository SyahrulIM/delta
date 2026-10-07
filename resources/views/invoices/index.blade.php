@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Purchase Orders</h5>
        <a href="{{ route('purchase_orders.create') }}" class="btn btn-primary">+ Create</a>
    </div>

    <div class="card-body">
        <table class="table table-bordered" id="poTable">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Total</th>
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
            { data: 'po_number' },
            { data: 'po_date' },
            {
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
                data: 'id',
                render: function(id, type, row){
                    return `
                        <a href="/purchase-orders/${id}" class="btn btn-info btn-sm">View</a>
                        <a href="/purchase-orders/${id}/print" class="btn btn-secondary btn-sm" target="_blank">Print</a>
                        <a href="/purchase-orders/${id}/edit" class="btn btn-warning btn-sm">Edit</a>
                        <button class="btn btn-danger btn-sm btn-delete" data-id="${id}">Del</button>
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
