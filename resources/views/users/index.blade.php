@extends('layouts.app')

@section('title', 'User')
@section('page-title', 'User')

@push('styles')
<style>
/* sedikit styling agar tabel rapi */
.table td { vertical-align: middle; }
</style>
@endpush

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Users</h5>
            <div>
                <button id="btnAdd" class="btn btn-primary">Add User</button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="table1" style="width:100%">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
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
<div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="projectForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="user_id" name="user_id">
        <code>Password Default : 12345678</code>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
            <div class="invalid-feedback" id="error-name"></div>
          </div>

          <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
            <div class="invalid-feedback" id="error-email"></div>
          </div>

          <div class="col-md-6 mb-3">
            <label for="role" class="form-label">Role</label>
            <select id="role" name="role" class="form-select" required>
              <option value="">Select Role</option>
              <option value="admin">Admin</option>
              <option value="purchase">Purchase</option>
              <option value="sales">Sales</option>
            </select>
            <div class="invalid-feedback" id="error-role"></div>
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
    const projectModalEl = document.getElementById('projectModal');
    const projectModal = new bootstrap.Modal(projectModalEl);

    // Initialize DataTable
    const table = $('#table1').DataTable({
        ajax: {
            url: '{{ route("users.data") }}',
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'name' },
            { data: 'email' },
            { data: 'role' },
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
        order: [[0, 'asc']],
        responsive: true,
    });

    // Clear validation errors
    function clearErrors() {
        $('#projectForm .form-control').removeClass('is-invalid');
        $('[id^="error-"]').text('');
    }

    // Open Add Modal
    $('#btnAdd').on('click', function() {
        $('#modalTitle').text('Add Project');
        $('#projectForm')[0].reset();
        $('#project_id').val('');
        clearErrors();
        projectModal.show();
    });

    // Open Edit
    $('#table1').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        clearErrors();
        $.get(`{{ url('users') }}/${id}`, function(res) {
            $('#modalTitle').text('Edit User');
            $('#user_id').val(res.id);
            $('#name').val(res.name);
            $('#email').val(res.email);
            $('#role').val(res.role);
            projectModal.show();
        }).fail(function() {
            toastError("Terjadi kesalahan server");
        });
    });

    // Save (Create or Update)
    $('#projectForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const id = $('#user_id').val();
        const url = id ? `{{ url('users') }}/${id}` : '{{ route("users.store") }}';
        const method = id ? 'PUT' : 'POST';

        const payload = {
            name: $('#name').val(),
            email: $('#email').val(),
            role: $('#role').val()
        };

        $.ajax({
            url: url,
            method: method,
            data: payload,
            success: function(resp) {
                projectModal.hide();
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
                    url: `{{ url('users') }}/${id}`,
                    method: 'DELETE',
                    success: function(resp) {
                        table.ajax.reload(null, false);
                        toastSuccess(resp.message ?? 'User deleted', 'success');

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
