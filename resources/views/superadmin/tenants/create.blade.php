@extends('layouts.superadmin')

@section('title', 'Create Tenant')

@section('content')
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Create New Tenant</h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row row-cards">
      <div class="col-12">
        <form action="{{ route('superadmin.tenants.store') }}" method="POST" class="card">
          @csrf
          <div class="card-body">
            <div class="row g-5">
              <div class="col-xl-6">
                <div class="mb-3">
                  <label class="form-label required">Business Name</label>
                  <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Kleening ID" value="{{ old('name') }}" required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-3">
                  <label class="form-label required">Slug (for URL prefix)</label>
                  <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" placeholder="e.g. kleening-id" value="{{ old('slug') }}" required>
                  <small class="form-hint">This will determine the URL structure: /slug/dashboard</small>
                  @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-3">
                  <label class="form-label">Custom Domain (optional)</label>
                  <input type="text" name="domain" class="form-control @error('domain') is-invalid @enderror" placeholder="e.g. my-business.com" value="{{ old('domain') }}">
                  @error('domain')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer text-end">
            <div class="d-flex">
              <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-link">Cancel</a>
              <button type="submit" class="btn btn-primary ms-auto">Create Tenant</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
