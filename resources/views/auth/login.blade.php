@extends('layouts.guest')

@section('content')
<div class="card card-md p-4">
  <div class="card-body">
    <div class="text-center mb-5">
        <h2 class="h1 mb-2">Welcome Back.</h2>
        <p class="text-muted">Login to your operational dashboard.</p>
    </div>
    <form method="POST" action="{{ route('login') }}" autocomplete="off" novalidate>
      @csrf

      @if (session('status'))
        <div class="alert alert-success" role="alert">
          {{ session('status') }}
        </div>
      @endif

      <div class="mb-4">
        <label class="form-label text-uppercase small" style="letter-spacing: 1px;">Phone Number</label>
        <input type="text" name="phone_number" class="form-control form-control-lg @error('phone_number') is-invalid @enderror" placeholder="0812..." value="{{ old('phone_number') }}" required autofocus>
        @error('phone_number')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="mb-4">
        <label class="form-label text-uppercase small" style="letter-spacing: 1px;">
          Password
          @if (Route::has('password.request'))
            <span class="form-label-description">
              <a href="{{ route('password.request') }}" class="text-primary">Forgot password?</a>
            </span>
          @endif
        </label>
        <input type="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" placeholder="••••••••" required>
        @error('password')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>
      <div class="mb-4">
        <label class="form-check">
          <input type="checkbox" name="remember" class="form-check-input"/>
          <span class="form-check-label">Remember me on this device</span>
        </label>
      </div>
      <div class="form-footer">
        <button type="submit" class="btn btn-primary btn-lg w-100 py-3">Sign in</button>
      </div>
    </form>
  </div>
</div>
@endsection