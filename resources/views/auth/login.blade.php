@extends('layouts.guest')

@section('content')
<div class="card card-md">
  <div class="card-body">
    <h2 class="h2 text-center mb-4">Login to your account</h2>
    <form method="POST" action="{{ route('login') }}" autocomplete="off" novalidate>
      @csrf

      @if (session('status'))
        <div class="alert alert-success" role="alert">
          {{ session('status') }}
        </div>
      @endif

      <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" placeholder="Enter phone number" value="{{ old('phone_number') }}" required autofocus>
        @error('phone_number')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="mb-2">
        <label class="form-label">
          Password
          @if (Route::has('password.request'))
            <span class="form-label-description">
              <a href="{{ route('password.request') }}">I forgot password</a>
            </span>
          @endif
        </label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Your password" required>
        @error('password')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="mb-2">
        <label class="form-check">
          <input type="checkbox" name="remember" class="form-check-input"/>
          <span class="form-check-label">Remember me on this device</span>
        </label>
      </div>
      <div class="form-footer">
        <button type="submit" class="btn btn-primary w-100">Sign in</button>
      </div>
    </form>
  </div>
</div>
@endsection