@extends('onboarding.layout')

@section('content')
<div class="card onboarding-card">
    <div class="card-header bg-white">
        <h3 class="card-title">Step 5: Initial Customer Database</h3>
    </div>
    <div class="card-body">
        <p class="text-muted">Register your existing customers to start creating service orders right away.</p>

        <div class="row g-4">
            <div class="col-md-6">
                <!-- Manual Form -->
                <form action="{{ route('onboarding.store', 'customer') }}" method="POST" class="onboarding-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Personal Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" placeholder="08..." required>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Address Label</label>
                        <input type="text" name="label" class="form-control" placeholder="e.g. Home, Office" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Operating Area</label>
                        <select name="area_id" class="form-select" required>
                            @foreach(\App\Models\Area::all() as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Full Address</label>
                        <textarea name="full_address" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Customer & Continue</button>
                </form>
            </div>
            <div class="col-md-6 border-start-md">
                <!-- CSV Upload -->
                <form action="{{ route('onboarding.store', 'customer') }}" method="POST" enctype="multipart/form-data" class="onboarding-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Upload Migration Database</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('onboarding.template', 'customer') }}" class="small">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                                <path d="M7 11l5 5l5 -5"></path>
                                <path d="M12 4l0 12"></path>
                            </svg>
                            Download Database Migration Template
                        </a>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">Import Database & Continue</button>
                    <div class="mt-4 p-3 bg-blue-lt rounded small text-blue">
                        Tip: You can skip this step by adding a single dummy data or just one customer to proceed.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
