@extends('layouts.admin')

@section('title', 'Laporan Pengeluaran')

@section('content')
    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">Laporan Pengeluaran</h2>
                    <div class="text-muted mt-1">Analisis pengeluaran operasional.</div>
                </div>
            </div>
        </div>

        <div class="page-body">
            {{-- Filter Section --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Periode Tanggal</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="filter-start-date">
                                <span class="input-group-text">to</span>
                                <input type="date" class="form-control" id="filter-end-date">
                            </div>
                        </div>
                        @if(in_array(auth()->user()->role, ['owner', 'admin']))
                            <div class="col-md-3">
                                <label class="form-label">Area</label>
                                <select class="form-select" id="filter-area">
                                    <option value="all">Semua Area</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-3">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" id="filter-category">
                                <option value="all">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="apply-filters">Terapkan</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-4">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto"><span class="bg-red text-white avatar">-</span></div>
                                <div class="col">
                                    <div class="font-weight-medium" id="summary-total-expenses">Rp 0</div>
                                    <div class="text-muted">Total Pengeluaran</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto"><span class="bg-blue text-white avatar">#</span></div>
                                <div class="col">
                                    <div class="font-weight-medium" id="summary-expense-count">0</div>
                                    <div class="text-muted">Jumlah Transaksi</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card card-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto"><span class="bg-yellow text-white avatar">!</span></div>
                                <div class="col">
                                    <div class="font-weight-medium" id="summary-most-expensive-category">N/A</div>
                                    <div class="text-muted">Kategori Terbesar</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Table --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="expense-report-table" class="table card-table table-vcenter text-nowrap datatable"
                            data-url="{{ route('data.reports.expenses') }}">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Pengeluaran</th>
                                    <th>Kategori</th>
                                    <th>Diinput Oleh</th>
                                    <th>Jumlah</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection