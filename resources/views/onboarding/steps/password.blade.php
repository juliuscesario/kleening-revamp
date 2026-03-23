@extends('onboarding.layout')

@section('content')
<div class="card onboarding-card text-center py-5">
    <div class="card-body">
        <div class="mb-4">
            <span class="avatar avatar-xl bg-green-lt text-green avatar-rounded">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-lock-check" width="64" height="64" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M11.5 21h-4.5a2 2 0 0 1 -2 -2v-6a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v.5"></path>
                    <path d="M11 16a1 1 0 1 0 2 0a1 1 0 1 0 -2 0"></path>
                    <path d="M8 11v-4a4 4 0 1 1 8 0v4"></path>
                    <path d="M15 19l2 2l4 -4"></path>
                </svg>
            </span>
        </div>
        
        <h2 class="card-title h1 mb-1">Set Your Secure Password</h2>
        <p class="text-muted mb-4">Complete your setup by creating your own private password. This will replace the initial password given by the administrator.</p>
        
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form action="{{ route('onboarding.store', 'password') }}" method="POST" class="onboarding-form">
                    @csrf
                    <div class="mb-3 text-start">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required minlength="6" placeholder="******">
                    </div>
                    <div class="mb-4 text-start">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="6" placeholder="******">
                    </div>
                    
                    <button type="submit" class="btn btn-green btn-lg w-100 py-3 shadow-sm hover-lift">
                        Complete Setup & Launch Dashboard
                    </button>
                    <div class="mt-3 small text-muted">A secure password will protect your tenant and your business data.</div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
