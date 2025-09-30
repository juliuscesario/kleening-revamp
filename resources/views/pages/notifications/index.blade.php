@extends('layouts.admin')

@section('title', 'All Notifications')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    All Notifications
                </h2>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="list-group list-group-flush">
                @forelse ($notifications as $notification)
                    <a href="{{ $notification->data['url'] ?? '#' }}" class="list-group-item list-group-item-action {{ $notification->read_at ? 'list-group-item-light' : '' }}">
                        <div class="d-flex w-100 justify-content-between">
                            <p class="mb-1">{{ $notification->data['message'] }}</p>
                            <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                    </a>
                @empty
                    <div class="list-group-item">
                        <p class="text-muted text-center m-3">You don't have any notifications yet.</p>
                    </div>
                @endforelse
            </div>
            @if($notifications->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
