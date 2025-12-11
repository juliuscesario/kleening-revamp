@extends('layouts.admin')
@section('title', 'Laporan Umur Piutang')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Laporan Umur Piutang</h2>
                <div class="text-muted mt-1">Analisis piutang berdasarkan kategori umur.</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Piutang</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table card-table table-vcenter text-nowrap datatable">
                        <thead>
                            <tr>
                                <th>Nomor Invoice</th>
                                <th>Pelanggan</th>
                                <th>Jumlah</th>
                                <th>Tanggal Jatuh Tempo</th>
                                <th>Hari Terlambat</th>
                                <th>Kategori Umur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                            <tr>
                                <td><a href="{{ route('web.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                                <td>{{ $invoice->serviceOrder->customer->name ?? 'N/A' }}</td>
                                <td>Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>
                                <td>{{ abs($invoice->days_overdue) }}</td>
                                <td>
                                    @php
                                        $badgeClass = '';
                                        if ($invoice->days_overdue < 0) { // Overdue
                                            $absDays = abs($invoice->days_overdue);
                                            if ($absDays <= 30) $badgeClass = 'bg-warning';
                                            elseif ($absDays <= 60) $badgeClass = 'bg-orange';
                                            elseif ($absDays <= 90) $badgeClass = 'bg-danger';
                                            else $badgeClass = 'bg-red';
                                        } else { // Not overdue
                                            $badgeClass = 'bg-success';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} text-bg-secondary">{{ $invoice->aging_bucket }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
