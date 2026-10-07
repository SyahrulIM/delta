@extends('layouts.app')

@section('content')
<section id="multiple-column-form">
    <div class="row match-height">
      <div class="col-12">
        <div class="card">

          <div class="card-header">
            <h4 class="card-title">
              {{ $projectId ? 'Edit Project' : 'Add New Project' }}
            </h4>
          </div>

          <div class="card-content">
            <div class="card-body">

              <form wire:submit.prevent="save" class="form">

                <div class="row">

                  <div class="col-md-6 col-12">
                    <div class="form-group mandatory">
                      <label class="form-label">Project Code</label>
                      <input wire:model="code" type="text" class="form-control">
                      @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>

                  <div class="col-md-6 col-12">
                    <div class="form-group mandatory">
                      <label class="form-label">Project Name</label>
                      <input wire:model="name" type="text" class="form-control">
                      @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                  </div>

                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label class="form-label">Location</label>
                      <input wire:model="location" type="text" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label class="form-label">Contact Name</label>
                      <input wire:model="contact_name" type="text" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-6 col-12">
                    <div class="form-group">
                      <label class="form-label">Contact Phone</label>
                      <input wire:model="contact_phone" type="text" class="form-control">
                    </div>
                  </div>

                </div>

                <div class="row mt-4">
                  <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary me-2">Save</button>
                    <a href="{{ route('projects.index') }}" class="btn btn-light-secondary">Back</a>
                  </div>
                </div>

              </form>

            </div>
          </div>

        </div>
      </div>
    </div>
</section>
@endsection
