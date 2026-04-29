@extends('layouts.admin')
@section('title', 'Operational Planner')

@php
function getCategoryShortCode($categoryName) {
    if (!$categoryName) return '?';
    $name = strtolower(trim($categoryName));
    $map = [
        'hydrovacuum' => 'HV', 'hydro vacuum' => 'HV', 'vacuum' => 'HV',
        'premium wash' => 'CC', 'premiumwash' => 'CC', 'cuci' => 'CC',
        'general cleaning' => 'GC', 'generalcleaning' => 'GC',
        'deep cleaning' => 'DC', 'deepcleaning' => 'DC',
        'car interior' => 'CID', 'car interior detailing' => 'CID',
        'poles' => 'Poles', 'polishing' => 'Poles',
        'survey' => 'Survey',
    ];
    foreach ($map as $key => $code) {
        if (str_contains($name, $key)) return $code;
    }
    return strtoupper(substr($categoryName, 0, 2));
}

function getCategoryBadgeClass($categoryName) {
    $code = strtolower(getCategoryShortCode($categoryName));
    $classMap = [
        'hv' => 'cat-hv', 'cc' => 'cat-cc', 'gc' => 'cat-gc',
        'dc' => 'cat-dc', 'cid' => 'cat-cid', 'poles' => 'cat-poles',
        'survey' => 'cat-survey',
    ];
    return $classMap[$code] ?? 'cat-default';
}

function getStatusLabel($status) {
    return match($status) {
        'booked' => 'Booked',
        'proses' => 'Proses',
        'done' => 'Done',
        'invoiced' => 'Invoiced',
        'sent' => 'Tagih',
        'overdue' => 'Blm Bayar',
        'paid' => 'Lunas',
        'cancelled' => 'Cancel',
        'inv_cancelled' => 'Inv.Cancel',
        default => ucfirst($status),
    };
}
@endphp

@push('styles')
<style>
    .page-header { background: var(--bg-surface); border-bottom: 1px solid var(--border-color); padding: 1rem 0; margin-bottom: 1.5rem; }
    
    /* Navigation */
    .planner-nav-container { display: flex; align-items: center; justify-content: space-between; gap: 1rem; width: 100%; }
    .planner-date-nav { display: flex; align-items: center; gap: .25rem; }
    .planner-date-label { font-size: 1.5rem; font-weight: 700; color: var(--text-main); letter-spacing: -0.8px; margin: 0 1rem; }
    
    .btn-nav-arrow { border: 1px solid var(--border-color); background: var(--bg-surface); padding: .25rem .5rem; border-radius: 4px; color: var(--text-secondary); }
    .btn-nav-arrow:hover { background: var(--bg-canvas); }

    .btn-refresh { border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--brand-primary); padding: .35rem .75rem; border-radius: 4px; font-weight: 500; font-size: .875rem; display: flex; align-items: center; gap: .5rem; }
    .btn-refresh:hover { background: var(--bg-canvas); }

    .planner-area-tabs { display: flex; border: 1px solid var(--border-color); border-radius: 4px; overflow: hidden; }
    .planner-area-tab { padding: .4rem .9rem; font-size: .875rem; font-weight: 500; color: var(--text-secondary); background: var(--bg-surface); border: none; border-right: 1px solid var(--border-color); text-decoration: none; transition: all 0.2s; }
    .planner-area-tab:last-child { border-right: none; }
    .planner-area-tab.active { background: var(--text-main) !important; color: var(--bg-surface) !important; }
    .planner-area-tab:hover:not(.active) { background: var(--bg-canvas); }

    .view-toggle { display: flex; border: 1px solid var(--border-color); border-radius: 4px; overflow: hidden; }
    .view-tab { padding: .4rem .9rem; font-size: .875rem; font-weight: 500; color: var(--text-secondary); background: var(--bg-surface); border: none; border-right: 1px solid var(--border-color); text-decoration: none; }
    .view-tab:last-child { border-right: none; }
    .view-tab.active { background: var(--text-main) !important; color: var(--bg-surface) !important; }

    /* Summary & Staff Chips */
    .planner-summary-header { font-size: .875rem; color: var(--text-secondary); margin-bottom: 1rem; cursor: pointer; }
    .planner-summary-header svg { transition: transform 0.2s; }
    .planner-summary-header[aria-expanded="true"] svg { transform: rotate(180deg); }

    .staff-chips-container { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.5rem; }
    .staff-chip { border-radius: 4px; padding: .35rem .75rem; font-size: .875rem; font-weight: 500; border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-secondary); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: .4rem; }
    .staff-chip:hover { transform: translateY(-1px); box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    
    .staff-chip.is-off { background: rgba(225, 29, 72, 0.1); color: #e11d48; border-color: rgba(225, 29, 72, 0.2); }
    .staff-chip.is-available { background: rgba(22, 163, 74, 0.1); color: #16a34a; border-color: rgba(22, 163, 74, 0.2); }
    .staff-chip.is-assigned { background: var(--bg-canvas); color: var(--text-main); border-color: var(--border-color); }
    
    .staff-chip-status-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
    .dot-off { background: #fb7185; }
    .dot-available { background: #4ade80; }
    .dot-assigned { background: #cbd5e1; }

    /* Legend */
    .planner-legend { display: flex; gap: 1rem; margin-bottom: 1.5rem; font-size: .75rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .legend-item { display: flex; align-items: center; gap: .4rem; }

    /* Table Design */
    .table-planner { background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 4px; border-collapse: separate; border-spacing: 0; }
    .table-planner th { background: var(--bg-surface); color: var(--text-secondary); font-weight: 600; font-size: .75rem; text-transform: uppercase; letter-spacing: 1.2px; padding: 1rem; border-bottom: 1px solid var(--border-color); }
    .table-planner td { padding: 1rem; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
    .table-planner tr:last-child td { border-bottom: none; }
    
    .job-time { font-size: 1rem; font-weight: 700; color: var(--text-main); }
    .customer-name { font-weight: 600; font-size: .9375rem; color: var(--brand-primary); text-decoration: none; }
    .wa-link { color: #16a34a; font-size: .75rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: .25rem; }
    .wa-link:hover { text-decoration: underline; }
    
    .lokasi-text { color: var(--text-secondary); font-size: .875rem; }
    .notes-text { color: var(--text-secondary); font-size: .8125rem; font-style: italic; }

    /* Category Badges */
    .cat-tag { font-size: .6875rem; font-weight: 700; padding: .2rem .4rem; border-radius: 4px; text-transform: uppercase; display: inline-block; margin: .1rem; border: 1px solid transparent; }
    .cat-hv { background: rgba(37, 99, 235, 0.1); color: #3b82f6; border-color: rgba(37, 99, 235, 0.2); }
    .cat-cc { background: rgba(124, 58, 237, 0.1); color: #8b5cf6; border-color: rgba(124, 58, 237, 0.2); }
    .cat-gc { background: rgba(22, 163, 74, 0.1); color: #22c55e; border-color: rgba(22, 163, 74, 0.2); }
    .cat-dc { background: rgba(217, 119, 6, 0.1); color: #f59e0b; border-color: rgba(217, 119, 6, 0.2); }
    
    /* Status Badges */
    .status-pill { font-size: .75rem; font-weight: 700; padding: .3rem .75rem; border-radius: 4px; display: inline-block; }
    .status-booked { background: rgba(126, 34, 206, 0.1); color: #a855f7; }
    .status-proses { background: rgba(194, 65, 12, 0.1); color: #f97316; }
    .status-overdue { background: rgba(29, 78, 216, 0.1); color: #3b82f6; }
    .status-paid { background: rgba(21, 128, 61, 0.1); color: #22c55e; }
    .status-cancelled { background: rgba(190, 18, 60, 0.1); color: #f43f5e; }

    .admin-pill { background: var(--bg-canvas); color: var(--text-secondary); border: 1px solid var(--border-color); font-size: .6875rem; font-weight: 600; padding: .2rem .5rem; border-radius: 4px; }

    /* Inline Edit */
    .inline-edit { cursor: pointer; border-bottom: 1px dashed var(--border-color); }
    .inline-edit:hover { background: var(--bg-canvas); }
    .inline-edit-input { font-size: .875rem; padding: .25rem .5rem; border: 1px solid var(--brand-primary); border-radius: 4px; width: 100%; outline: none; background: var(--bg-surface); color: var(--text-main); }
    /* Staff group header */
    .staff-group-header { display: flex; align-items: center; gap: 1rem; padding: 1.5rem 0 .5rem 0; border-bottom: 2px solid var(--text-main); margin-bottom: 1rem; }
    .staff-group-name { font-weight: 700; font-size: 1.25rem; color: var(--text-main); letter-spacing: -0.5px; }
    .staff-group-count { font-size: .875rem; font-weight: 600; color: var(--text-secondary); background: var(--bg-canvas); padding: .1rem .5rem; border-radius: 4px; }
    .staff-group-route { font-size: .75rem; font-weight: 500; color: var(--text-secondary); margin-left: auto; text-transform: uppercase; letter-spacing: 0.5px; }

    /* Job row (Staff View / Unassigned) */
    .job-row { display: flex; align-items: center; gap: 1rem; padding: .75rem 0; border-bottom: 1px solid var(--border-color); transition: background 0.2s; }
    .job-row:hover { background: var(--bg-canvas); }
    .job-row .job-time { width: 60px; flex-shrink: 0; }
    .job-row .job-main { flex: 1; min-width: 0; }
    .job-row .job-cats { display: flex; gap: .25rem; flex-wrap: wrap; width: 150px; flex-shrink: 0; }
    .job-row .job-status-col { width: 120px; flex-shrink: 0; text-align: right; }
    .job-row .job-actions { width: 40px; flex-shrink: 0; text-align: right; }

    .job-name-link { font-weight: 600; color: var(--brand-secondary); text-decoration: none; font-size: .9375rem; }
    .job-name-link:hover { text-decoration: underline; }

    @media (max-width: 992px) {
        .planner-nav-container { flex-direction: column; align-items: flex-start; }
        .planner-date-label { font-size: 1.25rem; margin: 0 .5rem; }
    }
</style>
@endpush

@section('content')
<div class="page-body">
    <div class="container-xl">
        {{-- Top bar: Date nav + Area tabs + View toggle --}}
        <div class="planner-nav-container mb-4">
            <div class="planner-date-nav">
                <a href="{{ route('web.planner.index', ['date' => $prevDate, 'area_id' => $areaId, 'view' => $viewMode]) }}" class="btn-nav-arrow">&lsaquo;</a>
                <div class="planner-date-label">
                    {{ $carbonDate->translatedFormat('j F Y') }}
                </div>
                <a href="{{ route('web.planner.index', ['date' => $nextDate, 'area_id' => $areaId, 'view' => $viewMode]) }}" class="btn-nav-arrow">&rsaquo;</a>
                
                <button onclick="location.reload()" class="btn-refresh ms-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                    Refresh
                </button>
            </div>

            <div class="d-flex gap-3 align-items-center flex-wrap">
                <div class="planner-area-tabs">
                    <a href="{{ route('web.planner.index', ['date' => $date, 'view' => $viewMode]) }}" class="planner-area-tab {{ !$areaId ? 'active' : '' }}">Semua</a>
                    @foreach($areas as $area)
                        <a href="{{ route('web.planner.index', ['date' => $date, 'area_id' => $area->id, 'view' => $viewMode]) }}" class="planner-area-tab {{ $areaId == $area->id ? 'active' : '' }}">{{ $area->name }}</a>
                    @endforeach
                </div>

                <div class="view-toggle">
                    <a href="{{ route('web.planner.index', ['date' => $date, 'area_id' => $areaId, 'view' => 'staff']) }}" class="view-tab {{ $viewMode === 'staff' ? 'active' : '' }}">Staff</a>
                    <a href="{{ route('web.planner.index', ['date' => $date, 'area_id' => $areaId, 'view' => 'list']) }}" class="view-tab {{ $viewMode === 'list' ? 'active' : '' }}">List</a>
                </div>
            </div>
        </div>

        {{-- Summary & Staff Chips --}}
        <div class="planner-summary-header" data-bs-toggle="collapse" href="#staffChipsCollapse" role="button" aria-expanded="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="m6 9 6 6 6-6"/></svg>
            Staff hari ini ({{ $totalStaffCount }} total · {{ $offStaffCount }} off · {{ $availableStaffCount }} available · {{ $assignedStaffCount }} assigned)
        </div>

        <div class="collapse show" id="staffChipsCollapse">
            <div class="staff-chips-container">
                @foreach($allStaff as $s)
                    @php
                        $isOff = $offDays->has($s->id);
                        $isAssigned = $assignedStaffIds->contains($s->id);
                        $jobCount = $isAssigned ? ($jobsByStaff->get($s->id)['jobs']->count() ?? 0) : 0;
                        
                        $chipClass = 'is-available';
                        $dotClass = 'dot-available';
                        $suffix = '';
                        
                        if ($isOff) {
                            $chipClass = 'is-off';
                            $dotClass = 'dot-off';
                            $suffix = '';
                        } elseif ($isAssigned) {
                            $chipClass = 'is-assigned';
                            $dotClass = 'dot-assigned';
                            $suffix = "($jobCount)";
                        }
                    @endphp
                    <div class="staff-chip {{ $chipClass }}" onclick="toggleStaffOffDirectly({{ $s->id }}, '{{ $date }}')">
                        @if($isOff)
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-danger"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        @else
                            <span class="staff-chip-status-dot {{ $dotClass }}"></span>
                        @endif
                        {{ $s->name }} {{ $suffix }}
                    </div>
                @endforeach
            </div>

            <div class="planner-legend">
                <div class="legend-item"><span class="staff-chip-status-dot dot-off"></span> OFF</div>
                <div class="legend-item"><span class="staff-chip-status-dot dot-available"></span> FREE</div>
                <div class="legend-item"><span class="staff-chip-status-dot dot-assigned"></span> ASSIGNED</div>
            </div>
        </div>

        {{-- Unassigned jobs --}}
        @if($unassignedJobs->count() > 0)
            <div class="mb-5">
                <div class="d-flex align-items-center gap-2 mb-3 text-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span class="fw-bold" style="text-transform:uppercase; letter-spacing:1.2px; font-size:.8125rem;">{{ $unassignedJobs->count() }} jobs unassigned</span>
                </div>
                <div class="table-planner w-100" style="border-bottom:none; background: #fff5f5; border-color: #feb2b2;">
                    <div style="padding: 0 1rem;">
                        @foreach($unassignedJobs as $so)
                            @include('pages.planner._job_row', ['so' => $so, 'showStaffCol' => true])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Main content: Staff view or List view --}}
        @if($viewMode === 'staff')
            {{-- STAFF VIEW --}}
            @forelse($jobsByStaff as $staffId => $group)
                <div class="staff-group-header">
                    <div class="staff-group-name">{{ $group['staff']->name }}</div>
                    <div class="staff-group-count">{{ $group['jobs']->count() }}</div>
                    @if($offDays->has($staffId))
                        <span class="status-pill status-cancelled">OFF</span>
                    @endif
                    <div class="staff-group-route desktop-only">{{ $group['route'] }}</div>
                </div>
                <div class="staff-group-route mobile-only mb-2">{{ $group['route'] }}</div>
                <div class="mb-4">
                    @foreach($group['jobs'] as $so)
                        @include('pages.planner._job_row', ['so' => $so, 'showStaffCol' => false])
                    @endforeach
                </div>
            @empty
                @if($unassignedJobs->count() === 0)
                    <div class="text-center text-muted py-5">
                        <div class="mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-12a2 2 0 0 1-2-2z"/><path d="M16 3v4"/><path d="M8 3v4"/><path d="M4 11h16"/><path d="M12 15h.01"/></svg>
                        </div>
                        Tidak ada pekerjaan untuk tanggal ini.
                    </div>
                @endif
            @endforelse
        @else
            {{-- LIST VIEW --}}
            <table class="table-planner w-100 mb-4">
                <thead>
                    <tr>
                        <th style="width:140px">Staff</th>
                        <th style="width:70px">Jam</th>
                        <th>Customer</th>
                        <th>Lokasi</th>
                        <th>Pekerjaan</th>
                        <th>Notes</th>
                        <th style="width:100px">Admin</th>
                        <th style="width:120px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($serviceOrders->where('status', '!=', 'cancelled') as $so)
                        @php
                            // Deduplicate categories for display
                            $pekerjaanCodes = [];
                            $pekerjaanDetails = [];
                            foreach ($so->items as $item) {
                                $code = getCategoryShortCode($item->service?->category?->name);
                                $pekerjaanCodes[$code] = true;
                                $fullServiceName = $item->service?->name ?? 'Unknown Service';
                                $pekerjaanDetails[] = $fullServiceName;
                            }
                            $uniqueCodes = array_keys($pekerjaanCodes);
                            $popoverContent = '<ul style="margin:0;padding-left:1rem;">';
                            foreach ($pekerjaanDetails as $detail) {
                                $popoverContent .= '<li>' . e($detail) . '</li>';
                            }
                            $popoverContent .= '</ul>';
                        @endphp
                        <tr data-so-id="{{ $so->id }}">
                            <td>
                                <span class="inline-edit fw-bold text-dark" onclick="editStaff(this, {{ $so->id }})" data-staff-ids="{{ $so->staff->pluck('id')->implode(',') }}">
                                    {{ $so->staff->pluck('name')->implode(', ') ?: 'Unassigned' }}
                                </span>
                            </td>
                            <td>
                                <span class="job-time inline-edit" onclick="editTime(this, {{ $so->id }})" data-value="{{ $so->work_time ? \Carbon\Carbon::createFromFormat('H:i:s', $so->work_time)->format('H:i') : '' }}">
                                    {{ $so->work_time ? \Carbon\Carbon::createFromFormat('H:i:s', $so->work_time)->format('H:i') : '—' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('web.service-orders.show', $so) }}" class="customer-name">{{ $so->customer?->name ?? '—' }}</a>
                                <div class="mt-1">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $so->customer?->phone_number ?? '') }}" target="_blank" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 py-0 px-2" style="font-size: 0.65rem; font-weight: 700; border-radius: 4px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        WHATSAPP
                                    </a>
                                </div>
                            </td>
                            <td><span class="lokasi-text">{{ $so->address?->lokasi ?? '—' }}</span></td>
                            <td>
                                @foreach($uniqueCodes as $code)
                                    <span class="cat-tag cat-{{ strtolower($code) }}"
                                          data-bs-toggle="popover"
                                          data-bs-trigger="click"
                                          data-bs-content="{!! htmlspecialchars($popoverContent) !!}"
                                          data-bs-html="true"
                                          style="cursor:pointer;">{{ $code }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="inline-edit notes-text" onclick="editNotes(this, {{ $so->id }})" data-value="{{ $so->staff_notes }}">
                                    {{ $so->staff_notes ?: '—' }}
                                </span>
                            </td>
                            <td><span class="admin-pill">{{ $so->creator?->name ? Str::limit($so->creator->name, 8) : '—' }}</span></td>
                            <td><span class="status-pill status-{{ $so->lifecycle_status }}">{{ getStatusLabel($so->lifecycle_status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada pekerjaan untuk tanggal ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        @endif

        {{-- Cancelled jobs (collapsed) --}}
        @if($cancelledJobs->count() > 0)
            <div class="cancelled-section">
                <a class="text-muted small" data-bs-toggle="collapse" href="#cancelledPanel" role="button">
                    {{ $cancelledJobs->count() }} cancelled jobs
                </a>
                <div class="collapse" id="cancelledPanel">
                    @foreach($cancelledJobs as $so)
                        @include('pages.planner._job_row', ['so' => $so, 'showStaffCol' => true])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Staff Assignment Modal --}}
<div class="modal modal-blur fade" id="staffModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="staffModalSoId">
                <div id="staffCheckboxes">
                    @foreach($allStaff as $s)
                        <label class="form-check mb-1">
                            <input class="form-check-input staff-checkbox" type="checkbox" value="{{ $s->id }}">
                            <span class="form-check-label">{{ $s->name }} <small class="text-muted">({{ $s->area?->name ?? '—' }})</small></span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" onclick="saveStaffAssignment()">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

function fetchJson(url, options = {}) {
    return fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
            ...options.headers,
        },
        ...options,
    }).then(r => r.json());
}

// Inline edit: work_time
function editTime(el, soId) {
    const current = el.dataset.value || '';
    const input = document.createElement('input');
    input.type = 'time';
    input.className = 'inline-edit-input';
    input.value = current;
    el.replaceWith(input);
    input.focus();

    function save() {
        const val = input.value;
        fetchJson(`/planner/${soId}/update-field`, {
            method: 'POST',
            body: JSON.stringify({ field: 'work_time', value: val }),
        }).then(data => {
            if (data.success) location.reload();
        });
    }
    input.addEventListener('blur', save);
    input.addEventListener('keydown', e => { if (e.key === 'Enter') save(); });
}

// Inline edit: notes
function editNotes(el, soId) {
    const current = el.dataset.value || '';
    const input = document.createElement('input');
    input.type = 'text';
    input.className = 'inline-edit-input';
    input.value = current;
    input.placeholder = 'Tambah catatan...';
    el.replaceWith(input);
    input.focus();

    function save() {
        const val = input.value;
        fetchJson(`/planner/${soId}/update-field`, {
            method: 'POST',
            body: JSON.stringify({ field: 'staff_notes', value: val }),
        }).then(data => {
            if (data.success) location.reload();
        });
    }
    input.addEventListener('blur', save);
    input.addEventListener('keydown', e => { if (e.key === 'Enter') save(); });
}

// Staff assignment modal
function editStaff(el, soId) {
    document.getElementById('staffModalSoId').value = soId;
    const currentIds = (el.dataset.staffIds || '').split(',').filter(Boolean);
    document.querySelectorAll('.staff-checkbox').forEach(cb => {
        cb.checked = currentIds.includes(cb.value);
    });
    const modal = new bootstrap.Modal(document.getElementById('staffModal'));
    modal.show();
}

function saveStaffAssignment() {
    const soId = document.getElementById('staffModalSoId').value;
    const selected = [...document.querySelectorAll('.staff-checkbox:checked')].map(cb => cb.value);
    if (selected.length === 0) {
        alert('Pilih minimal 1 staff');
        return;
    }
    fetchJson(`/planner/${soId}/update-staff`, {
        method: 'POST',
        body: JSON.stringify({ staff: selected }),
    }).then(data => {
        if (data.success) location.reload();
    });
}

// Toggle staff off day
function toggleStaffOff(btn) {
    const staffId = btn.dataset.staffId;
    const date = btn.dataset.date;
    fetchJson('/planner/toggle-staff-off', {
        method: 'POST',
        body: JSON.stringify({ staff_id: staffId, date: date }),
    }).then(data => {
        if (data.success) {
            btn.classList.toggle('is-off', data.is_off);
            // Reload to update summary
            setTimeout(() => location.reload(), 300);
        }
    });
}
function toggleStaffOffDirectly(staffId, date) {
    fetchJson('/planner/toggle-staff-off', {
        method: 'POST',
        body: JSON.stringify({ staff_id: staffId, date: date }),
    }).then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

// Initialize Bootstrap popovers for pekerjaan badges
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el) {
        new bootstrap.Popover(el, { html: true, trigger: 'click', placement: 'right' });
    });

    // Close popover when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('[data-bs-toggle="popover"]') && !e.target.closest('.popover')) {
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function(el) {
                const bsPopover = bootstrap.Popover.getInstance(el);
                if (bsPopover) bsPopover.hide();
            });
        }
    });
});
</script>
@endpush
