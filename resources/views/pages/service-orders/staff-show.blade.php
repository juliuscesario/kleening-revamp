@extends('layouts.admin')

@section('title', 'Detail Service Order')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Service Order #{{ $serviceOrder->so_number }}</h3>
                </div>
                <div class="card-body">
                    <div class="datagrid">
                        <div class="datagrid-item">
                            <div class="datagrid-title">Nomor SO</div>
                            <div class="datagrid-content">{{ $serviceOrder->so_number }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Tanggal Kerja</div>
                            <div class="datagrid-content">{{ \Carbon\Carbon::parse($serviceOrder->work_date)->format('d M Y') }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Status</div>
                            <div class="datagrid-content">{{ ucfirst($serviceOrder->status) }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Catatan Pekerjaan</div>
                            <div class="datagrid-content">{{ $serviceOrder->work_notes ?? '-' }}</div>
                        </div>
                        <div class="datagrid-item">
                            <div class="datagrid-title">Catatan Staff</div>
                            <div class="datagrid-content">{{ $serviceOrder->staff_notes ?? '-' }}</div>
                        </div>
                    </div>

                    <h4 class="mt-4">Daftar Layanan:</h4>
                    <ul class="list-group">
                        @foreach ($serviceOrder->items as $item)
                            <li class="list-group-item">
                                {{ $item->service->name }} ({{ $item->quantity }}x)
                            </li>
                        @endforeach
                    </ul>

                    <h4 class="mt-4">Staff yang Ditugaskan:</h4>
                    <ul class="list-group">
                        @foreach ($serviceOrder->staff as $s)
                            <li class="list-group-item">
                                {{ $s->user ? $s->user->name : '[User Tidak Tersedia]' }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">Kembali ke Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection