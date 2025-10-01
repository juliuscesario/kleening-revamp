<div class="row row-deck row-cards">
    {{-- KPIs --}}
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-green text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2" /><path d="M12 3v3m0 12v3" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}
                        </div>
                        <div class="text-muted">
                            Pendapatan Bulan Ini
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-blue text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 12l5 5l10 -10" /><path d="M2 12l5 5m5 -5l5 -5" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            {{ $jobsCompletedThisMonth }} Pekerjaan
                        </div>
                        <div class="text-muted">
                            Selesai Bulan Ini
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-orange text-white avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><line x1="9" y1="14" x2="15" y2="14" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            Rp {{ number_format($outstandingInvoices, 0, ',', '.') }}
                        </div>
                        <div class="text-muted">
                            Invoice Belum Dibayar
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-red text-white avatar">
                             <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-alert" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M12 17v.01" /><path d="M12 11l0 3" /></svg>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            Rp {{ number_format($overdueInvoices, 0, ',', '.') }}
                        </div>
                        <div class="text-muted">
                            Invoice Jatuh Tempo
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Service Order Funnel --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sales Funnel (30 Hari Terakhir)</h3>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Booked</span>
                    <span class="badge bg-secondary text-white">{{ $funnelBooked }}</span>
                </div>
                <div class="progress mt-1 mb-2" style="height: 5px;">
                    <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ $funnelBooked > 0 ? 100 : 0 }}%" aria-valuenow="{{ $funnelBooked }}"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Proses</span>
                    <span class="badge bg-info text-white">{{ $funnelProses }}</span>
                </div>
                <div class="progress mt-1 mb-2" style="height: 5px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $funnelProses > 0 ? 100 : 0 }}%" aria-valuenow="{{ $funnelProses }}"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Invoiced</span>
                    <span class="badge bg-success text-white">{{ $funnelInvoiced }}</span>
                </div>
                <div class="progress mt-1" style="height: 5px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $funnelInvoiced > 0 ? 100 : 0 }}%" aria-valuenow="{{ $funnelInvoiced }}"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span>Done</span>
                    <span class="badge bg-primary text-white">{{ $funnelDone }}</span>
                </div>
                <div class="progress mt-1" style="height: 5px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $funnelDone > 0 ? 100 : 0 }}%" aria-valuenow="{{ $funnelDone }}"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Revenue Chart --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Grafik Pendapatan Bulan Ini (Harian)</h3>
            </div>
            <div class="card-body">
                @php
                    $dates = $dailyRevenue->pluck('date')->map(function($date) { return \Carbon\Carbon::parse($date)->format('d M'); });
                    $totals = $dailyRevenue->pluck('total');
                @endphp
                <div id="chart-daily-revenue" class="chart-lg" data-dates='{{ json_encode($dates) }}' data-totals='{{ json_encode($totals) }}'></div>
            </div>
        </div>
    </div>
