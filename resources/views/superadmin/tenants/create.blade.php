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
                <div class="card-title">Tenant Information</div>
                <div class="mb-3">
                  <label class="form-label required">Business Name</label>
                  <input type="text" name="name" id="tenant_name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Kleening ID" value="{{ old('name') }}" required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-3">
                  <label class="form-label required">Slug (for URL prefix)</label>
                  <input type="text" name="slug" id="tenant_slug" class="form-control @error('slug') is-invalid @enderror" placeholder="e.g. kleening-id" value="{{ old('slug') }}" required>
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
                
                <div class="mb-3">
                  <label class="form-label">Areas of the Tenant</label>
                  <textarea name="areas" class="form-control @error('areas') is-invalid @enderror" rows="5" placeholder="Enter areas one per line...">{{ old('areas') }}</textarea>
                  <small class="form-hint">One area name per line (e.g. Jakarta Selatan, BSD, etc.)</small>
                  @error('areas')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>
              
              <div class="col-xl-6">
                <div class="card-title">Owner Information (Primary User)</div>
                <div class="mb-3">
                  <label class="form-label required">Owner Name</label>
                  <input type="text" name="owner_name" class="form-control @error('owner_name') is-invalid @enderror" placeholder="e.g. John Doe" value="{{ old('owner_name') }}" required>
                  @error('owner_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-3">
                  <label class="form-label required">Owner Phone Number</label>
                  <input type="text" name="owner_phone" class="form-control @error('owner_phone') is-invalid @enderror" placeholder="e.g. 08123456789" value="{{ old('owner_phone') }}" required>
                  <small class="form-hint">This will be used for login.</small>
                  @error('owner_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="mb-3">
                  <label class="form-label required">Initial Password</label>
                  <div class="input-group input-group-flat">
                    <input type="text" name="password" id="password_input" class="form-control @error('password') is-invalid @enderror" autocomplete="off" required>
                    <span class="input-group-text">
                      <a href="#" id="generate_password" class="link-secondary" title="Auto-generate" data-bs-toggle="tooltip">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                      </a>
                    </span>
                    @error('password')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tenantNameInput = document.getElementById('tenant_name');
    const tenantSlugInput = document.getElementById('tenant_slug');
    const generatePasswordBtn = document.getElementById('generate_password');
    const passwordInput = document.getElementById('password_input');

    // Slug generation logic
    tenantNameInput.addEventListener('input', function() {
        if (tenantSlugInput.value === '' || tenantSlugInput.dataset.auto === 'true') {
            const slug = this.value
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            tenantSlugInput.value = slug;
            tenantSlugInput.dataset.auto = 'true';
        }
    });

    tenantSlugInput.addEventListener('input', function() {
        tenantSlugInput.dataset.auto = 'false';
    });

    // Password generation logic
    function generateRandomPassword(length = 10) {
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
        let retVal = "";
        for (let i = 0, n = charset.length; i < length; ++i) {
            retVal += charset.charAt(Math.floor(Math.random() * n));
        }
        return retVal;
    }

    generatePasswordBtn.addEventListener('click', function(e) {
        e.preventDefault();
        passwordInput.value = generateRandomPassword();
    });

    // Generate initial password on load
    passwordInput.value = generateRandomPassword();
});
</script>
@endsection
