@extends('layouts.app')

@section('title', 'Customer')
@section('page-title', 'Customer')

@push('styles')
<style>
    /* sedikit styling agar tabel rapi */
    .table td {
        vertical-align: middle;
    }
</style>
@endpush

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Customers</h5>
            <div>
                <button id="btnAdd" class="btn btn-primary">Add Customer</button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="table1" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>NPWP</th>
                            <th>PIC</th>
                            <th width="140">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- Modal Form (Bootstrap) -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="customerForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="customer_id" name="customer_id">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Customer Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="address" class="form-label">Customer Address</label>
                        <input type="text" id="address" name="address" class="form-control" required>
                        <div class="invalid-feedback" id="error-address"></div>
                    </div>


                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control" required>
                        <div class="invalid-feedback" id="error-phone"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="npwp" class="form-label">NPWP</label>
                        <input type="text" id="npwp" name="npwp" class="form-control">
                        <div class="invalid-feedback" id="error-npwp"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pic" class="form-label">PIC</label>
                        <input type="text" id="pic" name="pic" class="form-control">
                        <div class="invalid-feedback" id="error-pic"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" id="btnSave" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Dependencies -->

<script>
    $(function() {

    // Initialize Bootstrap modal
    const customerModalEl = document.getElementById('customerModal');
    const customerModal = new bootstrap.Modal(customerModalEl);

    // Initialize DataTable
    const table = $('#table1').DataTable({
        ajax: {
            url: '{{ route("customers.data") }}',
            dataSrc: 'data'
        },
        columns: [
            { data: 'name' },
            { data: 'address' },
            { data: 'phone', defaultContent: '-' },
            { data: 'npwp', defaultContent: '-' },
            { data: 'pic', defaultContent: '-' },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return `
                      <button class="btn btn-sm btn-warning btn-edit" data-id="${data}">Edit</button>
                      <button class="btn btn-sm btn-danger btn-delete" data-id="${data}">Delete</button>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        responsive: true,
    });

    // Clear validation errors
    function clearErrors() {
        $('#customerForm .form-control').removeClass('is-invalid');
        $('[id^="error-"]').text('');
    }

    // Open Add Modal
    $('#btnAdd').on('click', function() {
        $('#modalTitle').text('Add Customer');
        $('#customerForm')[0].reset();
        $('#customer_id').val('');
        clearErrors();
        customerModal.show();
    });

    // Open Edit
    $('#table1').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        clearErrors();
        $.get(`{{ url('customers') }}/${id}`, function(res) {
            $('#modalTitle').text('Edit Customer');
            $('#customer_id').val(res.id);
            $('#name').val(res.name);
            $('#address').val(res.address);
            $('#phone').val(res.phone);
            $('#npwp').val(res.npwp);
            $('#pic').val(res.pic);
            customerModal.show();
        }).fail(function() {
            toastError("Terjadi kesalahan server");
        });
    });

    // Save (Create or Update)
    $('#customerForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const id = $('#customer_id').val();
        const url = id ? `{{ url('customers') }}/${id}` : '{{ route("customers.store") }}';
        const method = id ? 'PUT' : 'POST';

        const payload = {
            name: $('#name').val(),
            address: $('#address').val(),
            phone: $('#phone').val(),
            npwp: $('#npwp').val(),
            pic: $('#pic').val(),
        };

        $.ajax({
            url: url,
            method: method,
            data: payload,
            success: function(resp) {
                customerModal.hide();
                table.ajax.reload(null, false);

                toastSuccess(resp.message ?? 'Saved', 'success');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};
                    for (const key in errors) {
                        $(`#${key}`).addClass('is-invalid');
                        $(`#error-${key}`).text(errors[key][0]);
                    }
                    // show first error in toast
                    const first = Object.values(errors)[0][0];
                    toastWarning('Validation error', first, 'warning');
                } else {
                    toastError("Terjadi kesalahan server");
                }
            }
        });
    });

    // Delete
    $('#table1').on('click', '.btn-delete', function() {
        const id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "This action can't be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ url('customers') }}/${id}`,
                    method: 'DELETE',
                    success: function(resp) {
                        table.ajax.reload(null, false);
                        toastSuccess(resp.message ?? 'Customer deleted', 'success');

                    },
                    error: function() {
                        toastError("Failed to delete");
                    }
                });
            }
        });
    });

});
</script>
@endpush
