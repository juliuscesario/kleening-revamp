@extends('layouts.admin')
@section('title', 'Work Photos')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Work Photos</h2>
                <div class="text-muted mt-1">Arrival, before, and after photos from service orders.</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        {{-- Filter Bar --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row align-items-end g-2">
                    <div class="col-auto">
                        <form method="GET" action="{{ route('customers.work-photos.index') }}" class="d-flex align-items-end gap-2">
                            <div>
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}">
                            </div>
                            <div>
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}">
                            </div>
                            <button type="submit" class="btn btn-icon btn-primary" aria-label="Filter">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7.586a1 1 0 0 1 -.293 .707l-.707 .707a1 1 0 0 1 -1.414 0l-.707 -.707a1 1 0 0 1 -.293 -.707v-7.586l-4.414 -4.414a2 2 0 0 1 -.586 -1.414v-2.172z" /></svg>
                            </button>
                        </form>
                    </div>
                    <div class="col-auto ms-auto">
                        @if(count($groups) > 0)
                        <form method="POST" action="{{ route('customers.work-photos.download') }}">
                            @csrf
                            <input type="hidden" name="date_from" value="{{ $dateFrom }}">
                            <input type="hidden" name="date_to" value="{{ $dateTo }}">
                            <button type="submit" class="btn btn-icon btn-success" aria-label="Download ZIP">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Results --}}
        @forelse($groups as $soId => $group)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title me-auto">{{ $group['so_number'] }} — {{ $group['customer_name'] }}</h3>
                <form method="POST" action="{{ route('customers.work-photos.download-single', $soId) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download me-1" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                        Download
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Arrival --}}
                    <div class="col-md-4">
                        <h5 class="mb-2">Arrival</h5>
                        @if(!empty($group['arrival']))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($group['arrival'] as $photo)
                                    <img src="{{ asset('storage/' . $photo->file_path) }}"
                                         class="photo-thumb"
                                         data-full="{{ asset('storage/' . $photo->file_path) }}"
                                         alt="Arrival photo"
                                         style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; cursor: pointer;">
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">No photo</div>
                        @endif
                    </div>
                    {{-- Before --}}
                    <div class="col-md-4">
                        <h5 class="mb-2">Before</h5>
                        @if(!empty($group['before']))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($group['before'] as $photo)
                                    <img src="{{ asset('storage/' . $photo->file_path) }}"
                                         class="photo-thumb"
                                         data-full="{{ asset('storage/' . $photo->file_path) }}"
                                         alt="Before photo"
                                         style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; cursor: pointer;">
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">No photo</div>
                        @endif
                    </div>
                    {{-- After --}}
                    <div class="col-md-4">
                        <h5 class="mb-2">After</h5>
                        @if(!empty($group['after']))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($group['after'] as $photo)
                                    <img src="{{ asset('storage/' . $photo->file_path) }}"
                                         class="photo-thumb"
                                         data-full="{{ asset('storage/' . $photo->file_path) }}"
                                         alt="After photo"
                                         style="width: 150px; height: 150px; object-fit: cover; border-radius: 8px; cursor: pointer;">
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">No photo</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            No work photos found for this period.
        </div>
        @endforelse
    </div>
</div>

{{-- Lightbox Modal --}}
<div class="modal modal-blur fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: transparent; border: none; box-shadow: none;">
            <div class="modal-body text-center p-0">
                <img id="photoModalImg" src="" style="max-width: 100%; max-height: 90vh; border-radius: 8px;">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('photo-thumb')) {
        document.getElementById('photoModalImg').src = e.target.dataset.full;
        var modalEl = document.getElementById('photoModal');
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
});
</script>
@endpush
