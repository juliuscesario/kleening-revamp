@extends('layouts.admin')

@section('title', 'Daftar Pengeluaran')

@section('content')
    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">Daftar Pengeluaran</h2>
                    <div class="text-muted mt-1">Kelola data pengeluaran operasional.</div>
                </div>
                <div class="col-auto ms-auto d-print-none">
                    <div class="btn-list">
                        <a href="{{ route('web.expenses.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 5l0 14" />
                                <path d="M5 12l14 0" />
                            </svg>
                            Tambah Pengeluaran
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible" role="alert">
                    {{ session('success') }}
                    <a href="#" class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                </div>
            @endif

            <div class="card">
                <div class="table-responsive">
                    <table class="table card-table table-vcenter text-nowrap datatable">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Pengeluaran</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Dinput Oleh</th>
                                <th>Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->date->format('d M Y') }}</td>
                                    <td>
                                        <div class="font-weight-medium">{{ $expense->name }}</div>
                                        @if($expense->description)
                                            <div class="text-muted small">{{ Str::limit($expense->description, 50) }}</div>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-secondary text-white">{{ $expense->category->name }}</span></td>
                                    <td>Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                                    <td>{{ $expense->user->name }}</td>
                                    <td>
                                        @if($expense->photo_path)
                                            <a href="{{ asset('storage/' . $expense->photo_path) }}" target="_blank"
                                                class="btn btn-sm btn-ghost-secondary">
                                                Lihat Foto
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Belum ada data pengeluaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex align-items-center">
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection