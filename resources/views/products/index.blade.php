@extends('layouts.app')

@section('title', 'Master Product')
@section('page-title', 'Master Product')

@push('styles')
@endpush

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h5 class="card-title">Master Product</h5>
            <button id="btnAdd" class="btn btn-primary">Add Product</button>
        </div>

        <div class="card-body">
            <table class="table" id="tableProduct" style="width:100%">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Price</th>
                        <th>Cost</th>
                        <th width="130px">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</section>

{{-- MODAL --}}
<div class="modal fade" id="modalProduct" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" id="formProduct">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="product_id">

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" id="code">
                        <div class="text-danger small" id="error-code"></div>
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" id="name" class="form-control">
                        <div class="text-danger small" id="error-name"></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Qty</label>
                        <input type="number" id="qty" class="form-control" step="any">
                        <div class="text-danger small" id="error-qty"></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit</label>
                        <input type="text" id="unit" class="form-control">
                        <div class="text-danger small" id="error-unit"></div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Min Stock</label>
                        <input type="number" id="min_stock" class="form-control">
                        <div class="text-danger small" id="error-min_stock"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" id="price" class="form-control" step="any">
                        <div class="text-danger small" id="error-price"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cost</label>
                        <input type="number" id="cost" class="form-control" step="any">
                        <div class="text-danger small" id="error-cost"></div>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="btnSave">Save</button>
                <button class="btn btn-light-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')

{{-- Toast Helper --}}
<script>
window.toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});
window.toastSuccess = (msg)=> toast.fire({icon:'success',title:msg});
window.toastError   = (msg)=> toast.fire({icon:'error',title:msg});
window.toastWarning = (msg)=> toast.fire({icon:'warning',title:msg});
</script>

<script>
$(function() {

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });

    const modal = new bootstrap.Modal('#modalProduct');

    // DATATABLE
    let table = $('#tableProduct').DataTable({
        ajax: '{{ route("products.data") }}',
        columns: [
            { data: 'code' },
            { data: 'name' },
            { data: 'qty' },
            { data: 'unit' },
            {
                data: 'price',
                render: function(data){
                    return data?parseFloat(data.replace(',', '.')).toLocaleString('en-US'): '';
                }
            },
            {
                data: 'cost',
                render: function(data){
                    return data?parseFloat(data.replace(',', '.')).toLocaleString('en-US'): '';
                }
            },
            {
                data: 'id',
                render: (data)=> `
                    <button class="btn btn-warning btn-sm btn-edit" data-id="${data}">Edit</button>
                    <button class="btn btn-danger btn-sm btn-delete" data-id="${data}">Delete</button>
                `
            }
        ]
    });

    // CLEAR ERROR
    function clearError() {
        $('.text-danger').text('');
    }

    // ADD PRODUCT
    $('#btnAdd').click(function() {
        $('#modalTitle').text('Add Product');
        $('#formProduct')[0].reset();
        $('#product_id').val('');
        clearError();
        modal.show();
    });

    // EDIT PRODUCT
    $('#tableProduct').on('click', '.btn-edit', function() {
        let id = $(this).data('id');
        clearError();

        $.get(`/products/${id}`, function(res) {
            $('#modalTitle').text('Edit Product');

            $('#product_id').val(res.id);
            $('#code').val(res.code);
            $('#name').val(res.name);
            $('#qty').val(res.qty);
            $('#unit').val(res.unit);
            $('#min_stock').val(res.min_stock);
            $('#price').val(res.price);
            $('#cost').val(res.cost);

            modal.show();
        });
    });

    // SAVE (CREATE / UPDATE)
    $('#formProduct').submit(function(e) {
        e.preventDefault();
        clearError();

        let id = $('#product_id').val();
        let url = id ? `/products/${id}` : `{{ route('products.store') }}`;
        let method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: {
                code: $('#code').val(),
                name: $('#name').val(),
                qty: $('#qty').val(),
                unit: $('#unit').val(),
                min_stock: $('#min_stock').val(),
                price: $('#price').val(),
                cost: $('#cost').val(),
            },
            success: function(res) {
                modal.hide();
                table.ajax.reload(null, false);
                toastSuccess(res.message);
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    for (let field in errors) {
                        $(`#error-${field}`).text(errors[field][0]);
                    }
                    toastWarning("Please check your input");
                } else {
                    toastError("Server error!");
                }
            }
        });
    });

    // DELETE
    $('#tableProduct').on('click', '.btn-delete', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: "Delete Product?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Delete"
        }).then((result)=>{
            if (result.isConfirmed) {
                $.ajax({
                    url: `/products/${id}`,
                    type: "DELETE",
                    success: function(res) {
                        table.ajax.reload(null, false);
                        toastSuccess(res.message);
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
