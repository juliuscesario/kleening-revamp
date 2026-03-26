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
                        <label class="form-label font-bold">Customer Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Jane Smith" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bold">Personal Phone Number</label>
                        <input type="text" name="phone_number" class="form-control" placeholder="08..." required>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label font-bold">Address Label</label>
                            <input type="text" name="label" class="form-control" placeholder="e.g. Home, Office" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label font-bold">Operating Area</label>
                            <select name="area_id" class="form-select" required>
                                @foreach(\App\Models\Area::all() as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-bold">Full Address</label>
                        <textarea name="full_address" class="form-control" rows="2" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Customer</button>
                    <div class="mt-2 small text-muted">You can add more customers later in the Customer menu.</div>
                </form>

                <!-- Currently Added Customers -->
                <div class="mt-4">
                    <label class="form-label font-bold mb-2">Your Customers:</label>
                    @php $tenantCustomers = \App\Models\Customer::where('tenant_id', $tenant->id)->get(); $totalCustomers = $tenantCustomers->count(); @endphp
                    <div class="list-group list-group-flush border rounded-3 overflow-hidden">
                        @if($totalCustomers > 0)
                            @php 
                                if($totalCustomers > 20) {
                                    $firstN = $tenantCustomers->slice(0, 10);
                                    $lastN = $tenantCustomers->slice(-10);
                                }
                            @endphp
                            
                            @if($totalCustomers > 20)
                                @foreach($firstN as $tc)
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <div class="font-bold">{{ $tc->name }}</div>
                                            <div class="small text-muted">{{ $tc->phone_number }}</div>
                                        </div>
                                        <span class="badge bg-blue-lt">Added</span>
                                    </div>
                                @endforeach
                                <div class="list-group-item bg-gray-50 text-center py-2 small text-muted">
                                    ... showing {{ $totalCustomers - 20 }} more customers ...
                                </div>
                                @foreach($lastN as $tc)
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <div class="font-bold">{{ $tc->name }}</div>
                                            <div class="small text-muted">{{ $tc->phone_number }}</div>
                                        </div>
                                        <span class="badge bg-blue-lt">Added</span>
                                    </div>
                                @endforeach
                            @else
                                @foreach($tenantCustomers as $tc)
                                    <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <div class="font-bold">{{ $tc->name }}</div>
                                            <div class="small text-muted">{{ $tc->phone_number }}</div>
                                        </div>
                                        <span class="badge bg-blue-lt">Added</span>
                                    </div>
                                @endforeach
                            @endif
                        @else
                            <div class="list-group-item text-center py-3 text-muted bg-gray-50">
                                No customers added yet
                            </div>
                        @endif
                    </div>
                    @if($totalCustomers > 0)
                        <div class="mt-2 text-end small font-bold text-primary">Total: {{ $totalCustomers }} Customers</div>
                    @endif
                </div>
            </div>
            <div class="col-md-6 border-start-md">
                <!-- CSV Upload -->
                <form action="{{ route('onboarding.store', 'customer') }}" method="POST" enctype="multipart/form-data" class="onboarding-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label font-bold">Upload Migration Database</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('onboarding.template', 'customer') }}" class="small text-orange d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download me-1" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
                                <path d="M7 11l5 5l5 -5"></path>
                                <path d="M12 4l0 12"></path>
                            </svg>
                            Download Database Migration Template
                        </a>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100">Import Database</button>
                    <p class="mt-3 small text-muted">Use CSV for bulk updates. After successful import, the list will refresh.</p>
                </form>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white d-flex justify-content-end">
        <form action="{{ route('onboarding.complete', 'customer') }}" method="POST" class="onboarding-form">
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
