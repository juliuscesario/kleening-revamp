@extends('onboarding.layout')

@section('content')
<div class="card onboarding-card">
    <div class="card-header bg-white">
        <h3 class="card-title">Step 3: Service Categories</h3>
    </div>
    <div class="card-body">
        <p class="text-muted">Group your services into categories to help customers find what they need.</p>

        <div class="row g-4">
            <div class="col-md-6">
                <!-- Manual Form -->
                <form action="{{ route('onboarding.store', 'service_category') }}" method="POST" class="onboarding-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-bold">Category Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Cleaning, Maintenance" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Category</button>
                    <div class="mt-2 small text-muted">Example: Aircon, House Cleaning, General Maintenance</div>
                </form>

                <!-- Currently Added Categories -->
                <div class="mt-4">
                    <label class="form-label font-bold mb-2">Your Service Categories:</label>
                    @php $tenantCats = \App\Models\ServiceCategory::where('tenant_id', $tenant->id)->get(); $totalCats = $tenantCats->count(); @endphp
                    <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                        @if($totalCats > 0)
                            @php 
                                if($totalCats > 20) {
                                    $firstN = $tenantCats->slice(0, 10);
                                    $lastN = $tenantCats->slice(-10);
                                }
                            @endphp
                            
                            @if($totalCats > 20)
                                @foreach($firstN as $tc)
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div>{{ $tc->name }}</div>
                                        <span class="badge bg-blue-lt">Active</span>
                                    </div>
                                @endforeach
                                <div class="list-group-item bg-gray-50 text-center py-2 small text-muted">
                                    ... showing {{ $totalCats - 20 }} more categories ...
                                </div>
                                @foreach($lastN as $tc)
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div>{{ $tc->name }}</div>
                                        <span class="badge bg-blue-lt">Active</span>
                                    </div>
                                @endforeach
                            @else
                                @foreach($tenantCats as $tc)
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div>{{ $tc->name }}</div>
                                        <span class="badge bg-blue-lt">Active</span>
                                    </div>
                                @endforeach
                            @endif
                        @else
                            <div class="list-group-item text-center py-3 text-muted bg-gray-50">
                                No categories added yet
                            </div>
                        @endif
                    </div>
                    @if($totalCats > 0)
                        <div class="mt-2 text-end small font-bold text-primary">Total: {{ $totalCats }} Categories</div>
                    @endif
                </div>
            </div>
            <div class="col-md-6 border-start-md">
                <!-- CSV Upload -->
                <form action="{{ route('onboarding.store', 'service_category') }}" method="POST" enctype="multipart/form-data" class="onboarding-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-bold">Upload Categories CSV</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('onboarding.template', 'service_category') }}" class="small text-orange d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                                <path d="M7 11l5 5l5 -5"></path>
                                <path d="M12 4l0 12"></path>
                            </svg>
                            Download Example CSV
                        </a>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">Import Categories</button>
                    <p class="mt-3 small text-muted">Use CSV for bulk updates. After successful import, the list will refresh.</p>
                </form>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-end">
        <form action="{{ route('onboarding.complete', 'service_category') }}" method="POST" class="onboarding-form">
            @csrf
            <button type="submit" class="btn btn-primary d-flex align-items-center">
                Next Step
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-narrow-right ms-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M5 12l14 0"></path>
                    <path d="M15 16l4 -4"></path>
                    <path d="M15 8l4 4"></path>
                </svg>
            </button>
        </form>
    </div>
</div>
@endsection
