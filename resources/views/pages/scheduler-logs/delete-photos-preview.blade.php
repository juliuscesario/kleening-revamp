@extends('layouts.admin')

@section('title', 'Delete Oldest Images — Preview')

@section('content')
<div class="container-xl">
    <!-- Page-header -->
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Delete Oldest Images — Preview
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="mb-4">
                    <h3 class="card-title">Deletion Summary</h3>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><strong>Date Range:</strong> {{ $oldestDate->format('d M Y') }} → {{ $endDate->format('d M Y') }}</p>
                            <p><strong>Total Images to Delete:</strong> {{ $totalCount }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total File Size:</strong> {{ number_format($totalSize / 1024 / 1024, 2) }} MB</p>
                            <p><strong>Missing Files (already gone):</strong> {{ $missingFiles }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h3 class="card-title">Affected Service Orders</h3>
                    <div class="table-responsive mt-2">
                        <table class="table table-vcenter table-bordered">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Work Date</th>
                                    <th>Photo Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($affectedOrders as $item)
                                    <tr>
                                        <td>{{ $item->serviceOrder->so_number ?? 'N/A' }}</td>
                                        <td>{{ $item->serviceOrder->work_date ?? 'N/A' }}</td>
                                        <td>{{ $item->photo_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No affected orders.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('scheduler-logs.delete-photos.download') }}" class="btn btn-info">
                        Download Backup ZIP
                    </a>

                    <form action="{{ route('scheduler-logs.delete-photos.confirm') }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Are you sure? This will permanently delete {{ $totalCount }} photos. Make sure you have downloaded the backup first.')">
                        @csrf
                        <button type="submit" class="btn btn-danger">
                            Confirm Delete
                        </button>
                    </form>

                    <a href="{{ route('scheduler-logs.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
