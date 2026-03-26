@extends('layouts.guest')

@section('content')
<div class="card card-md">
  <div class="card-body">
    <h2 class="h2 text-center mb-4">SuperAdmin Login</h2>
    <form method="POST" action="{{ route('superadmin.login.store') }}" autocomplete="off" novalidate>
      @csrf

      <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" placeholder="Enter phone number" value="{{ old('phone_number') }}" required autofocus>
        @error('phone_number')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="mb-2">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Your password" required>
        @error('password')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="form-footer">
        <button type="submit" class="btn btn-primary w-100">Sign in to Manager</button>
      </div>
    </form>
  </div>
</div>
@endsection
