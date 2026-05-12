<div class="row row-deck row-cards">
    {{-- KPIs --}}
    <div class="col-6 col-lg-3">
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
    <div class="col-6 col-lg-3">
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
    <div class="col-6 col-lg-3">
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
    <div class="col-6 col-lg-3">
        <a href="{{ route('operational.pending.index') }}" class="text-decoration-none">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-{{ $soWithoutInvoice > 0 ? 'orange' : 'green' }} text-white avatar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-circle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 8v4" /><path d="M12 12h.01" /></svg>
                            </span>
                        </div>
                        <div class="col">
                            <div class="font-weight-medium">
                                {{ $soWithoutInvoice }}
                            </div>
                            <div class="text-muted">
                                SO Tanpa Invoice
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- Row 1b: Sales Funnel + Monthly Activity --}}
    <div class="row mt-3">
        {{-- Sales Funnel — keep content unchanged, shrink wrapper to col-lg-6 --}}
        <div class="col-lg-6">
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

        {{-- NEW: Monthly Activity --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aktivitas Bulan Ini</h3>
                    <div class="card-actions">
                        <span class="text-muted" style="font-size: 11px;">
                            vs {{ now()->subMonth()->translatedFormat('F') }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Left column: Service Orders --}}
                        <div class="col-6">
                            <div class="mb-2">
                                <span class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Service Order</span>
                            </div>
                            {{-- SO Dibuat --}}
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span>Dibuat</span>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $current = $soStats['created'] ?? 0;
                                        $previous = $soStatsLastMonth['created'] ?? 0;
                                        $diff = $current - $previous;
                                    @endphp
                                    <span class="text-muted" style="font-size: 11px;">{{ $previous }}</span>
                                    @if($diff > 0)
                                        <span class="text-success" style="font-size: 11px;">▲</span>
                                    @elseif($diff < 0)
                                        <span class="text-danger" style="font-size: 11px;">▼</span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">—</span>
                                    @endif
                                    <span class="badge bg-blue">{{ $current }}</span>
                                </div>
                            </div>
                            {{-- SO Done --}}
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span>Done</span>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $current = $soStats['done'] ?? 0;
                                        $previous = $soStatsLastMonth['done'] ?? 0;
                                        $diff = $current - $previous;
                                    @endphp
                                    <span class="text-muted" style="font-size: 11px;">{{ $previous }}</span>
                                    @if($diff > 0)
                                        <span class="text-success" style="font-size: 11px;">▲</span>
                                    @elseif($diff < 0)
                                        <span class="text-danger" style="font-size: 11px;">▼</span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">—</span>
                                    @endif
                                    <span class="badge bg-green">{{ $current }}</span>
                                </div>
                            </div>
                            {{-- SO Cancel — inverted: fewer is good --}}
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <span>Cancel</span>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $current = $soStats['cancel'] ?? 0;
                                        $previous = $soStatsLastMonth['cancel'] ?? 0;
                                        $diff = $current - $previous;
                                    @endphp
                                    <span class="text-muted" style="font-size: 11px;">{{ $previous }}</span>
                                    @if($diff > 0)
                                        <span class="text-danger" style="font-size: 11px;">▲</span>
                                    @elseif($diff < 0)
                                        <span class="text-success" style="font-size: 11px;">▼</span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">—</span>
                                    @endif
                                    <span class="badge bg-red">{{ $current }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Right column: Invoices --}}
                        <div class="col-6">
                            <div class="mb-2">
                                <span class="text-muted" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Invoice</span>
                            </div>
                            {{-- Invoice Dibuat --}}
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span>Dibuat</span>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $current = $invoiceStats['created'] ?? 0;
                                        $previous = $invoiceStatsLastMonth['created'] ?? 0;
                                        $diff = $current - $previous;
                                    @endphp
                                    <span class="text-muted" style="font-size: 11px;">{{ $previous }}</span>
                                    @if($diff > 0)
                                        <span class="text-success" style="font-size: 11px;">▲</span>
                                    @elseif($diff < 0)
                                        <span class="text-danger" style="font-size: 11px;">▼</span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">—</span>
                                    @endif
                                    <span class="badge bg-blue">{{ $current }}</span>
                                </div>
                            </div>
                            {{-- Invoice Sent --}}
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span>Sent</span>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $current = $invoiceStats['sent'] ?? 0;
                                        $previous = $invoiceStatsLastMonth['sent'] ?? 0;
                                        $diff = $current - $previous;
                                    @endphp
                                    <span class="text-muted" style="font-size: 11px;">{{ $previous }}</span>
                                    @if($diff > 0)
                                        <span class="text-success" style="font-size: 11px;">▲</span>
                                    @elseif($diff < 0)
                                        <span class="text-danger" style="font-size: 11px;">▼</span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">—</span>
                                    @endif
                                    <span class="badge bg-info text-white">{{ $current }}</span>
                                </div>
                            </div>
                            {{-- Invoice Paid --}}
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span>Paid</span>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $current = $invoiceStats['paid'] ?? 0;
                                        $previous = $invoiceStatsLastMonth['paid'] ?? 0;
                                        $diff = $current - $previous;
                                    @endphp
                                    <span class="text-muted" style="font-size: 11px;">{{ $previous }}</span>
                                    @if($diff > 0)
                                        <span class="text-success" style="font-size: 11px;">▲</span>
                                    @elseif($diff < 0)
                                        <span class="text-danger" style="font-size: 11px;">▼</span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">—</span>
                                    @endif
                                    <span class="badge bg-green">{{ $current }}</span>
                                </div>
                            </div>
                            {{-- Invoice Cancel — inverted: fewer is good --}}
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span>Cancel</span>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $current = $invoiceStats['cancel'] ?? 0;
                                        $previous = $invoiceStatsLastMonth['cancel'] ?? 0;
                                        $diff = $current - $previous;
                                    @endphp
                                    <span class="text-muted" style="font-size: 11px;">{{ $previous }}</span>
                                    @if($diff > 0)
                                        <span class="text-danger" style="font-size: 11px;">▲</span>
                                    @elseif($diff < 0)
                                        <span class="text-success" style="font-size: 11px;">▼</span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">—</span>
                                    @endif
                                    <span class="badge bg-red">{{ $current }}</span>
                                </div>
                            </div>
                            {{-- Invoice Overdue --}}
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <span>Overdue</span>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $current = $invoiceStats['overdue'] ?? 0;
                                        $previous = $invoiceStatsLastMonth['overdue'] ?? 0;
                                        $diff = $current - $previous;
                                    @endphp
                                    <span class="text-muted" style="font-size: 11px;">{{ $previous }}</span>
                                    @if($diff > 0)
                                        <span class="text-danger" style="font-size: 11px;">▲</span>
                                    @elseif($diff < 0)
                                        <span class="text-success" style="font-size: 11px;">▼</span>
                                    @else
                                        <span class="text-muted" style="font-size: 11px;">—</span>
                                    @endif
                                    <span class="badge bg-orange">{{ $current }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: Revenue & Cash Flow --}}
    <div class="row mt-3">
        {{-- Card A: Revenue Trend --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pendapatan 6 Bulan Terakhir</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartRevenueTrend" height="260"></canvas>
                </div>
            </div>
        </div>

        {{-- Card B: Revenue by Category --}}
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Revenue per Kategori (Bulan Ini)</h3>
                </div>
                <div class="card-body" style="position:relative;">
                    <canvas id="chartRevenueCategory" height="260"></canvas>
                </div>
            </div>
        </div>

        {{-- Card C: Invoice Aging --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Aging Invoice Belum Dibayar</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartInvoiceAging" height="260"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 3: Admin Accountability --}}
    <div class="row mt-3" id="row-admin">
        {{-- Card D: Admin Scorecard --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Performa Admin (Bulan Ini)</h3>
                </div>
                <div class="card-body p-0">
                    @if(count($adminScorecard ?? []) > 0)
                        <table class="table table-vcenter card-table">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th class="text-center">Invoice</th>
                                    <th class="text-center">Avg SO→Invoice</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $maxAvg = collect($adminScorecard)->max('avg_turnaround') ?? 0;
                                @endphp
                                @foreach($adminScorecard as $admin)
                                    <tr>
                                        <td>{{ $admin['name'] }}</td>
                                        <td class="text-center">{{ $admin['invoices_count'] }}</td>
                                        <td class="text-center">
                                            @if($admin['avg_turnaround'] > 3)
                                                <span class="text-red">
                                                    {{ $admin['avg_turnaround'] }} hari ⚠️
                                                </span>
                                            @elseif($maxAvg > 0 && $admin['avg_turnaround'] == $maxAvg)
                                                <span class="text-red">{{ $admin['avg_turnaround'] }} hari</span>
                                            @else
                                                {{ $admin['avg_turnaround'] }} hari
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center text-muted py-3">Tidak ada admin</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card E: Bottleneck Alerts --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">⚠️ Perlu Perhatian</h3>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($bottlenecks ?? [] as $item)
                        @php
                            $iconClass = $item['severity'] === 'danger' ? 'text-danger' : 'text-warning';
                        @endphp
                        <a href="{{ $item['url'] }}" class="list-group-item list-group-item-action">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="ti ti-alert-circle {{ $iconClass }}" style="font-size: 1.5rem;"></i>
                                </div>
                                <div class="col">
                                    <div class="fw-bold">{{ $item['label'] }}</div>
                                    <div class="text-muted small">{{ $item['detail'] }}</div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center text-muted py-3">
                            ✅ Semua lancar — tidak ada item tertunda
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Row 4: Staff Performance --}}
    <div class="row mt-3" id="row-staff">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Performa Staff (Bulan Ini)</h3>
                </div>
                <div class="card-body p-0">
                    @if(isset($staffLeaderboard) && count($staffLeaderboard) > 0)
                        @php
                            $groupedByArea = collect($staffLeaderboard)->groupBy('area');
                            $maxSessions = collect($staffLeaderboard)->max('sessions') ?? 0;
                        @endphp
                        @foreach($groupedByArea as $areaName => $staffInArea)
                                <div class="card-header">
                                    <h3 class="card-title text-primary">Area: {{ $areaName }}</h3>
                                </div>
                                <table class="table table-vcenter card-table mb-3">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:50px">#</th>
                                            <th>Staff</th>
                                            <th class="text-center" style="width:80px">Jobs</th>
                                            <th class="text-center" style="width:140px">Foto Compliance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                @foreach($staffInArea as $s)
                                    <tr>
                                        <td class="text-center text-muted">{{ $loop->parent->iteration }}.{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $s['name'] }}
                                            @if($s['sessions'] == $maxSessions && $maxSessions > 0)
                                                <span class="ms-1">🏆</span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-bold">{{ $s['sessions'] }}</td>
                                        <td class="text-center">
                                            @if($s['photo_compliance'] >= 100)
                                                <span class="badge bg-green">{{ $s['photo_compliance'] }}%</span>
                                            @elseif($s['photo_compliance'] >= 80)
                                                <span class="badge bg-yellow">{{ $s['photo_compliance'] }}%</span>
                                            @else
                                                <span class="badge bg-red">{{ $s['photo_compliance'] }}%</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                    </tbody>
                                </table>
                            @endforeach
                    @else
                        <div class="text-center text-muted py-3">Tidak ada data staff aktif bulan ini</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Row 5: Customer Insights --}}
    <div class="row mt-3" id="row-customer">
        {{-- Card H: Customer Overview --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pelanggan (Bulan Ini)</h3>
                </div>
                <div class="card-body">
                    @if(isset($customerOverview))
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar me-3" style="width:40px;height:40px;background:#e7f5ff;color:#1B9CFC;">👥</span>
                            <div>
                                <div class="text-muted">Total Pelanggan</div>
                                <div class="fs-3 fw-bold">{{ $customerOverview['total'] }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar me-3" style="width:40px;height:40px;background:#d3f9d8;color:#2f9e44;">🆕</span>
                            <div>
                                <div class="text-muted">Pelanggan Baru</div>
                                <div class="fs-3 fw-bold">{{ $customerOverview['new'] }}</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <span class="avatar me-3" style="width:40px;height:40px;background:#fff3cd;color:#f59f00;">🔄</span>
                            <div>
                                <div class="text-muted">Pelanggan Repeat</div>
                                <div class="fs-3 fw-bold">{{ $customerOverview['repeat'] }}</div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Repeat Rate</span>
                            @php
                                $rr = $customerOverview['repeat_rate'];
                                $rrClass = $rr >= 50 ? 'bg-green' : ($rr >= 30 ? 'bg-yellow' : 'bg-red');
                            @endphp
                            <span class="badge {{ $rrClass }} fs-2">{{ $rr }}%</span>
                        </div>
                    @else
                        <div class="text-muted text-center">No data</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card I: Top Customers --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Top 5 Pelanggan</h3>
                </div>
                <div class="card-body p-0">
                    @if(isset($topCustomers) && $topCustomers->count() > 0)
                        <table class="table table-vcenter card-table mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:40px">#</th>
                                    <th>Nama</th>
                                    <th class="text-center" style="width:80px">Total Order</th>
                                    <th class="text-end" style="width:150px">Total Revenue</th>
                                    <th class="text-center" style="width:120px">Terakhir Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCustomers as $c)
                                    <tr>
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td class="fw-bold">{{ $c->name }}</td>
                                        <td class="text-center">{{ $c->total_orders }}</td>
                                        <td class="text-end">Rp {{ number_format($c->total_revenue, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if($c->days_since_last === null)
                                                <span class="text-muted">-</span>
                                            @elseif($c->days_since_last > 60)
                                                <span class="text-danger fw-bold">⚠️ {{ round($c->days_since_last) }} hari lalu</span>
                                            @elseif($c->days_since_last <= 30)
                                                <span class="text-success">{{ round($c->days_since_last) }} hari lalu</span>
                                            @else
                                                <span class="text-warning">{{ round($c->days_since_last) }} hari lalu</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-muted text-center py-4">No data</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
(function() {
    const formatRupiah = function(val) {
        if (val >= 1000000000) return 'Rp ' + (val / 1000000000).toFixed(1) + 'B';
        if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + 'M';
        if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + 'K';
        return 'Rp ' + val;
    };

    const formatRupiahShort = function(val) {
        if (val >= 1000000000) return (val / 1000000000).toFixed(1) + 'M';
        if (val >= 1000000) return (val / 1000000).toFixed(1) + 'Jt';
        if (val >= 1000) return (val / 1000).toFixed(0) + 'rb';
        return val.toString();
    };

    // ── Chart A: Revenue Trend (Line) ──
    const trendCanvas = document.getElementById('chartRevenueTrend');
    if (trendCanvas) {
        const rawTrend = @json($revenueTrend);
        const trendKeys = Object.keys(rawTrend);
        const trendVals = Object.values(rawTrend);

        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const labels = trendKeys.map(k => {
            const [y, m] = k.split('-');
            return monthNames[parseInt(m, 10) - 1] + ' ' + y.slice(2);
        });

        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan',
                    data: trendVals,
                    borderColor: '#1B9CFC',
                    backgroundColor: 'rgba(27, 156, 252, 0.10)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: val => formatRupiahShort(val)
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ── Chart B: Revenue by Category (Doughnut) ──
    const catCanvas = document.getElementById('chartRevenueCategory');
    if (catCanvas) {
        const rawCat = @json($revenueByCategory);
        const catLabels = Object.keys(rawCat);
        const catVals = Object.values(rawCat).map(v => parseFloat(v) || 0);

        const doughnutColors = [
            '#1B9CFC', '#20c997', '#fd7e14', '#e64980',
            '#7950f2', '#fab005', '#2f9e44', '#e8590c',
            '#0c8599', '#6741d9'
        ];

        const catChart = new Chart(catCanvas, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catVals,
                    backgroundColor: doughnutColors.slice(0, catLabels.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                layout: {
                    padding: 5
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 8, usePointStyle: true, pointStyleWidth: 8, font: { size: 10 }, boxWidth: 8 }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.label + ': Rp ' + ctx.parsed.toLocaleString('id-ID')
                        }
                    }
                }
            },
            plugins: [{
                id: 'centerText',
                afterDraw: function(chart) {
                    const { ctx, width, height } = chart;
                    ctx.save();

                    const total = chart.data.datasets[0].data.reduce((sum, val) => sum + val, 0);

                    let formatted;
                    if (total >= 1000000) {
                        formatted = 'Rp ' + (total / 1000000).toFixed(1).replace('.', ',') + 'M';
                    } else if (total >= 1000) {
                        formatted = 'Rp ' + (total / 1000).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.') + 'rb';
                    } else {
                        formatted = 'Rp ' + total.toLocaleString('id-ID');
                    }

                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#868e96';
                    ctx.font = '11px sans-serif';
                    ctx.fillText('Total', width / 2, height / 2 - 8);

                    ctx.fillStyle = '#495057';
                    ctx.font = 'bold 12px sans-serif';
                    ctx.fillText(formatted, width / 2, height / 2 + 8);

                    ctx.restore();
                }
            }]
        });
    }

    // ── Chart C: Invoice Aging (Horizontal Bar) ──
    const agingCanvas = document.getElementById('chartInvoiceAging');
    if (agingCanvas) {
        const rawAging = @json($invoiceAging);
        const agingLabels = Object.keys(rawAging);
        const agingVals = Object.values(rawAging);
        const agingColors = ['#2f9e44', '#fab005', '#fd7e14', '#e03131'];

        new Chart(agingCanvas, {
            type: 'bar',
            data: {
                labels: agingLabels,
                datasets: [{
                    data: agingVals,
                    backgroundColor: agingColors.slice(0, agingLabels.length),
                    borderRadius: 4,
                    barThickness: 28,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => 'Rp ' + ctx.parsed.x.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: val => formatRupiahShort(val)
                        },
                        grid: { display: false }
                    },
                    y: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
})();
</script>
@endpush

