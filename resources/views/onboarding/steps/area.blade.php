@extends('onboarding.layout')

@section('content')
<div class="card onboarding-card">
    <div class="card-header bg-white">
        <h3 class="card-title">Step 1: Setup Your Operating Areas</h3>
    </div>
    <div class="card-body">
        <p class="text-muted">Define the regions where your business operates. You can use existing official areas or register your own.</p>
        
        <!-- Global Areas List -->
        @php 
            $globalAreas = \App\Models\Area::withoutGlobalScope('tenant')->whereNull('tenant_id')->get();
        @endphp
        
        @if($globalAreas->count() > 0)
        <div class="mb-4">
            <label class="form-label font-bold mb-2">Available Official Areas:</label>
            <div class="list-group">
                @foreach($globalAreas as $ga)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="status status-success status-dot me-2"></span>
                        {{ $ga->name }}
                    </div>
                    <span class="badge bg-green-lt">Ready to Use</span>
                </div>
                @endforeach
            </div>
        </div>
        <hr class="my-4">
        @endif

        <div class="row g-4">
            <div class="col-md-6">
                <!-- Manual Form -->
                <form action="{{ route('onboarding.store', 'area') }}" method="POST" class="onboarding-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Add Area Manually</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Jakarta Selatan" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Area & Continue</button>
                    <div class="mt-2 small text-muted">You can add more areas later in settings.</div>
                </form>
            </div>
            <div class="col-md-6 border-start-md">
                <!-- CSV Upload -->
                <form action="{{ route('onboarding.store', 'area') }}" method="POST" enctype="multipart/form-data" class="onboarding-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Upload CSV</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('onboarding.template', 'area') }}" class="small">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                                <path d="M7 11l5 5l5 -5"></path>
                                <path d="M12 4l0 12"></path>
                            </svg>
                            Download Example CSV
                        </a>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">Import CSV & Continue</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
