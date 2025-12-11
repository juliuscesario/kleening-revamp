@extends('layouts.admin')

@section('title', 'Scheduler Logs')

@section('content')
<div class="container-xl">
    <!-- Page-header -->
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Scheduler Logs
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <div class="mb-4 flex justify-end space-x-2">
                    <form action="{{ route('scheduler-logs.run') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="command" value="invoices:mark-overdue">
                        <button type="submit" class="btn btn-primary">
                            Run: Mark Invoices as Overdue
                        </button>
                    </form>
                    <form action="{{ route('scheduler-logs.run') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="command" value="service-orders:auto-cancel-old">
                        <button type="submit" class="btn btn-primary">
                            Run: Auto Cancel Old Service Orders
                        </button>
                    </form>
                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Command</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Items Processed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($logs as $log)
                                <tr>
                                    <td>{{ $log->command }}</td>
                                    <td>{{ $log->start_time }}</td>
                                    <td>{{ $log->end_time }}</td>
                                    <td>{{ $log->items_processed }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection