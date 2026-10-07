@extends('layouts.app')

@section('title', 'Project')
@section('page-title', 'Project')

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
            <h5 class="card-title m-0">Projects</h5>
            <a href="{{ route('projects.export') }}" class="btn btn-success">
                Export Excel
            </a>
            <div>
                <button id="btnAdd" class="btn btn-primary">Add Project</button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="table1" style="width:100%">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Company Name</th>
                            <th>Location</th>
                            <th>Contact</th>
                            <th>Phone</th>
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
        <h5 class="modal-title" id="modalTitle">Add Project</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="project_id" name="project_id">

        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="code" class="form-label">Project Code</label>
            <input type="text" id="code" name="code" class="form-control" required>
            <div class="invalid-feedback" id="error-code"></div>
          </div>

          <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Project Name</label>
            <input type="text" id="name" name="name" class="form-control" required>
            <div class="invalid-feedback" id="error-name"></div>
          </div>

          <div class="col-md-6 mb-3">
            <label for="customer_name" class="form-label">Company Name</label>
            <input type="text" id="customer_name" name="customer_name" class="form-control" required>
            <div class="invalid-feedback" id="error-customer_name"></div>
          </div>

          <div class="col-md-6 mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" id="location" name="location" class="form-control">
            <div class="invalid-feedback" id="error-location"></div>
          </div>

          <div class="col-md-6 mb-3">
            <label for="contact_name" class="form-label">Contact Name</label>
            <input type="text" id="contact_name" name="contact_name" class="form-control">
            <div class="invalid-feedback" id="error-contact_name"></div>
          </div>

          <div class="col-md-6 mb-3">
            <label for="contact_phone" class="form-label">Contact Phone</label>
            <input type="text" id="contact_phone" name="contact_phone" class="form-control">
            <div class="invalid-feedback" id="error-contact_phone"></div>
          </div>

          <div class="col-md-6 mb-3">
            <label for="job" class="form-label">Job</label>
            <input type="text" id="job" name="job" class="form-control">
            <div class="invalid-feedback" id="error-job"></div>
          </div>
          <div class="col-md-6 mb-3">
            <label for="contract_no" class="form-label">Contract No</label>
            <input type="text" id="contract_no" name="contract_no" class="form-control">
            <div class="invalid-feedback" id="error-contract_no"></div>
          </div>

          <div class="col-md-6 mb-3">
            <label for="npwp" class="form-label">NPWP</label>
            <input type="text" id="npwp" name="npwp" class="form-control">
            <div class="invalid-feedback" id="error-npwp"></div>
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
            url: '{{ route("projects.data") }}',
            dataSrc: 'data'
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'code' },
            { data: 'name' },
            { data: 'customer_name' },
            { data: 'location', defaultContent: '-' },
            { data: 'contact_name', defaultContent: '-' },
            { data: 'contact_phone', defaultContent: '-' },
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
        $.get(`{{ url('projects') }}/${id}`, function(res) {
            $('#modalTitle').text('Edit Project');
            $('#project_id').val(res.id);
            $('#code').val(res.code);
            $('#name').val(res.name);
            $('#customer_name').val(res.customer_name);
            $('#location').val(res.location);
            $('#contact_name').val(res.contact_name);
            $('#contact_phone').val(res.contact_phone);
            $('#job').val(res.job);
            $('#npwp').val(res.npwp);
            $('#contract_no').val(res.contract_no);
            projectModal.show();
        }).fail(function() {
            toastError("Terjadi kesalahan server");
        });
    });

    // Save (Create or Update)
    $('#projectForm').on('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const id = $('#project_id').val();
        const url = id ? `{{ url('projects') }}/${id}` : '{{ route("projects.store") }}';
        const method = id ? 'PUT' : 'POST';

        const payload = {
            code: $('#code').val(),
            name: $('#name').val(),
            customer_name: $('#customer_name').val(),
            location: $('#location').val(),
            contact_name: $('#contact_name').val(),
            contact_phone: $('#contact_phone').val(),
            job: $('#job').val(),
            npwp: $('#npwp').val(),
            contract_no: $('#contract_no').val()
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
                    url: `{{ url('projects') }}/${id}`,
                    method: 'DELETE',
                    success: function(resp) {
                        table.ajax.reload(null, false);
                        toastSuccess(resp.message ?? 'Project deleted', 'success');

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
