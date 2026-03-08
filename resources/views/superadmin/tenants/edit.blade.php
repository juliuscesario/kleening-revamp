@extends('layouts.superadmin')

@section('title', 'Edit Tenant')

@section('content')
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Edit Tenant: {{ $tenant->name }}</h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row row-cards">
      <div class="col-12">
        <form action="{{ route('superadmin.tenants.update', $tenant) }}" method="POST" class="card">
          @csrf
          @method('PUT')
          <div class="card-body">
            <div class="row g-5">
              <div class="col-xl-6">
                <div class="card-title">Tenant Information</div>
                <div class="mb-3">
                  <label class="form-label required">Business Name</label>
                  <input type="text" name="name" id="tenant_name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Kleening ID" value="{{ old('name', $tenant->name) }}" required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-3">
                  <label class="form-label required">Slug (for URL prefix)</label>
                  <input type="text" name="slug" id="tenant_slug" class="form-control @error('slug') is-invalid @enderror" placeholder="e.g. kleening-id" value="{{ old('slug', $tenant->slug) }}" required>
                  <small class="form-hint">This will determine the URL structure: /slug/dashboard</small>
                  @error('slug')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-3">
                  <label class="form-label">Custom Domain (optional)</label>
                  <input type="text" name="domain" class="form-control @error('domain') is-invalid @enderror" placeholder="e.g. my-business.com" value="{{ old('domain', $tenant->domain) }}">
                  @error('domain')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              
              <div class="col-xl-6">
                <div class="card-title">Owner Information</div>
                <div class="mb-3">
                  <label class="form-label required">Owner Name</label>
                  <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" placeholder="e.g. John Doe" value="{{ old('owner_name', $owner ? $owner->name : '') }}" required>
                  @error('owner_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-3">
                  <label class="form-label required">Owner Phone Number</label>
                  <input type="text" name="owner_phone" class="form-control @error('owner_phone') is-invalid @enderror" placeholder="e.g. 08123456789" value="{{ old('owner_phone', $owner ? $owner->phone_number : '') }}" required>
                  <small class="form-hint">This is used for login.</small>
                  @error('owner_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                
                <div class="mt-4">
                    <div class="alert alert-info">
                        Areas can be managed directly through the tenant's dashboard.
                    </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer text-end">
            <div class="d-flex">
              <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-link">Cancel</a>
              <button type="submit" class="btn btn-primary ms-auto">Update Tenant</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tenantNameInput = document.getElementById('tenant_name');
    const tenantSlugInput = document.getElementById('tenant_slug');

    // Slug generation logic (only if empty or manually triggered?)
    // For edit page, we probably don't want to auto-change slug unless the user clears it.
    tenantNameInput.addEventListener('input', function() {
        if (tenantSlugInput.value === '') {
            const slug = this.value
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            tenantSlugInput.value = slug;
        }
    });
});
</script>
@endsection
