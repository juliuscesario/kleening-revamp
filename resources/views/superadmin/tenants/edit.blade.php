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

                <div class="hr-text">Reset Password</div>
                
                <div class="mb-3">
                  <label class="form-label">New Password</label>
                  <div class="input-group input-group-flat">
                    <input type="text" name="password" id="password_input" class="form-control @error('password') is-invalid @enderror" placeholder="Leave blank to keep current password" autocomplete="off">
                    <span class="input-group-text">
                      <a href="#" id="generate_password" class="link-secondary" title="Auto-generate" data-bs-toggle="tooltip">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                      </a>
                    </span>
                    @error('password')
                      <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                  </div>
                  <small class="form-hint">Type a new password or click the icon to generate one.</small>
                </div>
                <div class="mb-3">
                  <label class="form-label">Confirm New Password</label>
                  <input type="text" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm new password">
                </div>
                
                <div class="mt-4">
                    <div class="alert alert-info">
                        Areas can be managed directly through the tenant's dashboard.
                    </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer d-flex">
            <a href="{{ route('superadmin.tenants.index') }}" class="btn btn-link">Cancel</a>
            <div class="ms-auto">
              <button type="submit" class="btn btn-primary">Update Tenant</button>
            </div>
          </div>
        </form>

        @if($tenant->onboarding_completed_at)
        <div class="card mt-4 border-warning">
          <div class="card-body">
            <div class="row align-items-center text-center text-md-start">
              <div class="col-md-9 mb-3 mb-md-0">
                <h4 class="mb-1 text-warning">Reset Onboarding Process</h4>
                <p class="text-muted mb-0">This will allow the tenant owner to go through the 7-step onboarding process again. Existing data will <strong>not</strong> be deleted, but they must verify each step.</p>
              </div>
              <div class="col-md-3 text-md-end">
                <form action="{{ route('onboarding.reset', ['tenant' => $tenant->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to reset the onboarding process for this tenant?')">
                  @csrf
                  <button type="submit" class="btn btn-warning">Reset Onboarding</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        @else
        <div class="card mt-4">
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-9">
                <h4 class="mb-1">Onboarding in Progress</h4>
                <p class="text-muted mb-0">The tenant owner is currently completing the onboarding steps. You can force reset if they are stuck.</p>
              </div>
              <div class="col-md-3 text-md-end">
                <form action="{{ route('onboarding.reset', ['tenant' => $tenant->id]) }}" method="POST" onsubmit="return confirm('Reset current progress?')">
                  @csrf
                  <button type="submit" class="btn btn-outline-danger">Reset Progress</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        @endif
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
    const passwordConfirmationInput = document.getElementById('password_confirmation');

    // Slug generation logic
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
        const pwd = generateRandomPassword();
        passwordInput.value = pwd;
        passwordConfirmationInput.value = pwd;
    });
});
</script>
@endsection
