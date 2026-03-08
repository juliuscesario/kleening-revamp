@extends('layouts.superadmin')

@section('title', 'Manage Tenants')

@section('content')
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">Tenants Management</h2>
      </div>
      <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
          <a href="{{ route('superadmin.tenants.create') }}" class="btn btn-primary d-none d-sm-inline-block">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none">
              <path d="M12 5l0 14" /><path d="M5 12l14 0" />
            </svg>
            Create New Tenant
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
      <div class="table-responsive">
        <table class="table table-vcenter table-mobile-md card-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Slug</th>
              <th>Domain</th>
              <th>URL Prefix</th>
              <th class="w-1"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($tenants as $tenant)
              <tr>
                <td>{{ $tenant->name }}</td>
                <td class="text-muted">{{ $tenant->slug }}</td>
                <td>{{ $tenant->domain ?: '-' }}</td>
                <td>/{{ $tenant->slug }}</td>
                <td>
                  <div class="btn-list flex-nowrap">
                    <form action="{{ route('superadmin.tenants.destroy', $tenant) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this tenant?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
            @if($tenants->isEmpty())
              <tr>
                <td colspan="5" class="text-center">No tenants found.</td>
              </tr>
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
