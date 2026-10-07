@extends('layouts.app')

@section('title', 'Purchase Requests')
@section('page-title', 'Purchase Requests')

@push('styles')
<style>
    /* sedikit styling agar tabel rapi */
    .table td {
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Purchase Requests</h5>
        <a href="{{ route('purchase_requests.create') }}" class="btn btn-primary">+ Create</a>
    </div>

    <div class="card-body">
        <table class="table table-brequested" id="prTable">
            <thead>
                <tr>
                    <th>JOB No</th>
                    <th>Company Name</th>
                    <th>Project</th>
                    <th>PR Number</th>
                    <th>Date</th>
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

    let table = $('#prTable').DataTable({
        ajax: "{{ route('purchase_requests.data') }}",
        columns: [
            {
                data: 'project',
                render: function(data){
                    return data?.job ?? '-';
                }
            },
            {
                data: 'project',
                render: function(data){
                    return data?.name ?? '-';
                }
            },
            {
                data: 'project',
                render: function(data){
                    return data?.customer_name ?? '-';
                }
            },
            { data: 'pr_number' },
            { data: 'pr_date' },
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
                render: function(id, type, row){
                    // <a href="/purchase-requests/${id}" class="btn btn-info btn-sm">View</a>
                    return `
                        <a href="/purchase-requests/${id}/print" class="btn btn-secondary btn-sm" target="_blank">Print</a>
                        <a href="/purchase-requests/${id}/edit" class="btn btn-warning btn-sm">Edit</a>
                        <button class="btn btn-danger btn-sm btn-delete" data-id="${id}">Del</button>
                    `;
                },
                requestable: false,
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
                    url: `/purchase-requests/${id}`,
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
