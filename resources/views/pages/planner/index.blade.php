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
        'cancelled' => 'Cancel',
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

    #planner-date-heading { cursor: pointer; text-decoration: underline dotted; text-underline-offset: 4px; transition: opacity 0.15s; }
    #planner-date-heading:hover,
    #planner-date-heading:active { opacity: 0.75; }

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
    .notes-edit-btn { display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary); opacity: 0; transition: opacity 0.2s; margin-left: 4px; vertical-align: middle; }
    .notes-text-container:hover .notes-edit-btn { opacity: 1; }
    .notes-edit-btn:hover { color: var(--brand-primary); }
    @media (max-width: 768px) {
        .notes-edit-btn { opacity: 1 !important; }
    }

    /* Lokasi Badge */
    .lokasi-badge { display: inline-block; font-size: .75rem; font-weight: 500; padding: .2rem .5rem; border-radius: 4px; min-height: 32px; line-height: 1.5; cursor: pointer; }
    .lokasi-badge { background: rgba(100, 116, 139, 0.1); color: #64748b; border: 1px solid rgba(100, 116, 139, 0.2); }
    .lokasi-badge-empty { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2) !important; }
    .alamat-text { font-size: .6875rem; color: var(--text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }

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
    .status-done { background: rgba(22, 163, 74, 0.1); color: #16a34a; }
    .status-inv_cancelled { background: rgba(190, 18, 60, 0.1); color: #be123c; }

    /* Session badges */
    .session-num-badge { font-size: .65rem; font-weight: 700; padding: .15rem .4rem; border-radius: 3px; background: rgba(100, 116, 139, 0.1); color: #64748b; white-space: nowrap; }
    .session-num-badge-mobile { display: none; font-size: .6rem; font-weight: 700; padding: 1px 3px; border-radius: 2px; background: rgba(100, 116, 139, 0.1); color: #64748b; }

    .session-type-badge { font-size: .6rem; font-weight: 700; padding: .1rem .35rem; border-radius: 3px; text-transform: uppercase; white-space: nowrap; }
    .session-type-badge.type-pickup { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
    .session-type-badge.type-delivery { background: rgba(22, 163, 74, 0.1); color: #22c55e; border: 1px solid rgba(22, 163, 74, 0.2); }
    .session-type-badge.type-survey { background: rgba(168, 85, 247, 0.1); color: #a855f7; border: 1px solid rgba(168, 85, 247, 0.2); }


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

    /* ==========================================
       MOBILE LAYOUT FIX — Planner Job Cards
       ========================================== */
    @media (max-width: 768px) {
        /* Job row: stack vertically on mobile */
        .job-row {
            flex-wrap: wrap !important;
            gap: 4px 8px;
            padding: 10px 12px;
            position: relative;
        }

        /* Time: small, top-left */
        .job-row .job-time {
            width: auto !important;
            min-width: unset;
            flex: 0 0 auto;
            font-size: 0.85rem;
        }

        /* Main content (name + lokasi + notes): take remaining space on first line */
        .job-row .job-main {
            flex: 1 1 0;
            min-width: 0;
            overflow: hidden;
        }

        .job-row .job-main .job-name-link {
            display: inline;
            word-break: break-word;
        }

        .job-row .job-main .wa-link,
        .job-row .job-main .btn-outline-success {
            font-size: 0.7rem;
        }

        .job-row .job-main .lokasi-badge {
            font-size: 0.75rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .job-row .job-main .alamat-text {
            font-size: 0.75rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .job-row .job-main .notes-text {
            font-size: 0.75rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        /* Category badges: new line, left-aligned */
        .job-row .job-cats {
            flex: 0 0 100%;
            order: 10;
            display: flex;
            flex-wrap: wrap;
            gap: 3px;
            margin-top: 2px;
        }

        .job-row .job-cats .cat-tag {
            font-size: 0.65rem;
            padding: 1px 5px;
        }

        /* Status badge: position top-right */
        .job-row .job-status-col {
            position: absolute;
            top: 10px;
            right: 12px;
            width: auto !important;
            flex: none;
        }

        .job-row .job-status-col .status-pill {
            font-size: 0.7rem;
            padding: 2px 6px;
        }

        /* Session badges: show compact version on mobile */
        .session-num-badge { display: none !important; }
        .session-num-badge-mobile { display: inline-block !important; }
        .session-type-badge { font-size: 0.55rem; padding: 1px 3px; }

        /* Staff column in list view: hide on mobile (staff already shown in section header) */
        .job-row .job-staff-col {
            display: none;
        }

        /* Actions: position near status */
        .job-row .job-actions {
            position: absolute;
            top: 10px;
            right: 80px;
            width: auto !important;
            flex: none;
        }

        /* Staff section header: stack route summary below name */
        .staff-group-header {
            flex-wrap: wrap;
        }

        .staff-group-header .staff-group-route {
            font-size: 0.75rem;
            margin-left: 0;
            margin-top: 2px;
        }

        /* Inline edit inputs: full width on mobile */
        .inline-edit-input {
            width: 100%;
            max-width: 100%;
            font-size: 16px; /* Prevents iOS zoom on input focus */
        }

        /* Make tap targets bigger on mobile */
        .inline-edit {
            min-height: 32px;
            display: inline-flex;
            align-items: center;
        }

        /* List view: let columns auto-size on mobile */
        .table-planner th,
        .table-planner td {
            white-space: nowrap;
            padding: 6px 8px;
            font-size: 0.8rem;
        }
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
                <span id="planner-date-heading" class="planner-date-label" title="Pilih tanggal">
                    <i class="ti ti-calendar me-1" style="font-size:0.8em; vertical-align:middle;"></i>
                    {{ $carbonDate->translatedFormat('j F Y') }}
                </span>
                <a href="{{ route('web.planner.index', ['date' => $nextDate, 'area_id' => $areaId, 'view' => $viewMode]) }}" class="btn-nav-arrow">&rsaquo;</a>

                {{-- Hidden native date picker --}}
                <input type="date"
                       id="planner-date-picker"
                       value="{{ $carbonDate->format('Y-m-d') }}"
                       style="position:absolute; opacity:0; width:0; height:0; pointer-events:none;">
                
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
                        $jobCount = $isAssigned ? ($sessionsByStaff->get($s->id)['sessions']->count() ?? 0) : 0;
                        
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
                    <div class="staff-chip {{ $chipClass }}" data-staff-id="{{ $s->id }}" onclick="toggleStaffOffDirectly({{ $s->id }}, '{{ $date }}')">
                        @if($isOff)
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="text-danger"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        @else
                            <span class="staff-chip-status-dot {{ $dotClass }}"></span>
                        @endif
                        <span>
                            {{ $s->name }} {{ $suffix }}
                            @if(isset($activeAttendances[$s->id]) && $activeAttendances[$s->id])
                                <small class="text-muted d-block" style="font-size:.75rem;">{{ $activeAttendances[$s->id] }}</small>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="planner-legend">
                <div class="legend-item"><span class="staff-chip-status-dot dot-off"></span> OFF</div>
                <div class="legend-item"><span class="staff-chip-status-dot dot-available"></span> FREE</div>
                <div class="legend-item"><span class="staff-chip-status-dot dot-assigned"></span> ASSIGNED</div>
            </div>
        </div>

        {{-- Unassigned sessions --}}
        @if($unassignedSessions->count() > 0)
            <div class="mb-5">
                <div class="d-flex align-items-center gap-2 mb-3 text-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span class="fw-bold" style="text-transform:uppercase; letter-spacing:1.2px; font-size:.8125rem;">{{ $unassignedSessions->count() }} sessions unassigned</span>
                </div>
                <div class="table-planner w-100" style="border-bottom:none; background: #fff5f5; border-color: #feb2b2;">
                    <div style="padding: 0 1rem;">
                        @foreach($unassignedSessions as $session)
                            @include('pages.planner._session_row', ['session' => $session, 'showStaffCol' => true, 'viewMode' => $viewMode])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Main content: Staff view or List view --}}
        @if($viewMode === 'staff')
            {{-- STAFF VIEW --}}
            @forelse($sessionsByStaff as $staffId => $group)
                <div class="staff-group-header">
                    <div class="staff-group-name">
                        {{ $group['staff']->name }}
                        @if(isset($activeAttendances[$staffId]) && $activeAttendances[$staffId])
                            <small class="text-muted d-block" style="font-size:.75rem; font-weight:500;">{{ $activeAttendances[$staffId] }}</small>
                        @endif
                    </div>
                    <div class="staff-group-count">{{ $group['sessions']->count() }}</div>
                    @if($offDays->has($staffId))
                        <span class="status-pill status-cancelled">OFF</span>
                    @endif
                    <div class="staff-group-route desktop-only">{{ $group['route'] }}</div>
                </div>
                <div class="staff-group-route mobile-only mb-2">{{ $group['route'] }}</div>
                <div class="mb-4">
                    @foreach($group['sessions'] as $session)
                        @include('pages.planner._session_row', ['session' => $session, 'showStaffCol' => false, 'viewMode' => $viewMode])
                    @endforeach
                </div>
            @empty
                @if($unassignedSessions->count() === 0)
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
            <div class="table-responsive">
                <table class="table-planner w-100 mb-4">
                <thead>
                    <tr>
                        <th style="width:120px">Staff</th>
                        <th style="width:65px">Jam</th>
                        <th>Customer</th>
                        <th>Lokasi</th>
                        <th style="width:90px">Pekerjaan</th>
                        <th>Sesi</th>
                        <th>Notes</th>
                        <th style="width:90px">Phone</th>
                        <th style="width:80px">Admin</th>
                        <th style="width:120px">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                        @php
                            $so = $session->serviceOrder;
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
                        <tr data-session-id="{{ $session->id }}" data-so-id="{{ $so->id }}">
                            <td>
                                @php
                                    $staffNames = $session->staff->pluck('name')->implode(', ') ?: 'Unassigned';
                                    $staffMachineCodes = $session->staff->filter(fn($s) => isset($activeAttendances[$s->id]) && $activeAttendances[$s->id])->map(fn($s) => $s->name . ': ' . $activeAttendances[$s->id])->join(', ');
                                @endphp
                                <span class="inline-edit fw-bold text-dark" onclick="editStaff(this, {{ $session->id }})" data-staff-ids="{{ $session->staff->pluck('id')->implode(',') }}" @if($staffMachineCodes) title="{{ $staffMachineCodes }}" @endif>
                                    {{ $staffNames }}
                                </span>
                            </td>
                            <td>
                                <span class="job-time inline-edit" onclick="editTime(this, {{ $session->id }})" data-value="{{ $session->jam ? \Carbon\Carbon::parse($session->jam)->format('H:i') : '' }}">
                                    {{ $session->jam ? \Carbon\Carbon::parse($session->jam)->format('H:i') : '—' }}
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
                            <td>
                                @php
                                    $lokasiValue = $so->address?->lokasi;
                                    $alamatValue = Str::limit($so->address?->full_address ?? '', 30);
                                @endphp
                                @if($lokasiValue)
                                    <span class="lokasi-badge inline-edit" onclick="editLokasi(this, {{ $so->id }}, {{ $so->address?->id ?? 'null' }})" data-value="{{ $lokasiValue }}" data-address-id="{{ $so->address?->id ?? '' }}">
                                        {{ $lokasiValue }}
                                    </span>
                                @else
                                    <span class="lokasi-badge lokasi-badge-empty inline-edit" onclick="editLokasi(this, {{ $so->id }}, {{ $so->address?->id ?? 'null' }})" data-value="" data-address-id="{{ $so->address?->id ?? '' }}">
                                        Lokasi?
                                    </span>
                                @endif
                                @if($alamatValue)
                                    <div class="alamat-text"
                                         data-bs-toggle="popover"
                                         data-bs-trigger="click"
                                         data-bs-content="{{ $so->address?->full_address ?? '' }}"
                                         style="cursor:pointer;">
                                        {{ $alamatValue }}
                                    </div>
                                @endif
                            </td>
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
                                @if($so->is_multi_session)
                                    @php $totalSessions = $so->sessions()->count(); @endphp
                                    <span class="session-num-badge">S{{ $session->session_number }}/{{ $totalSessions }}</span>
                                @endif
                                @if($session->type !== 'kerja')
                                    <span class="session-type-badge type-{{ $session->type }}">{{ strtoupper($session->type) }}</span>
                                @endif
                                @if(!$so->is_multi_session && $session->type === 'kerja')
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($so->staff_notes)
                                    <span class="notes-text-container" style="position: relative; display: inline-block;">
                                        <span class="notes-text" data-bs-toggle="popover" data-bs-trigger="click" data-bs-content="{{ e($so->staff_notes) }}" style="cursor: pointer;">
                                            {{ Str::limit($so->staff_notes, 40, '…') }}
                                        </span>
                                        <span class="notes-edit-btn" onclick="editNotes(this, {{ $session->id }})" data-value="{{ e($so->staff_notes) }}" data-full="{{ e($so->staff_notes) }}" title="Edit catatan">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                        </span>
                                    </span>
                                @else
                                    <span class="inline-edit notes-text text-muted" data-value="" data-full="" onclick="editNotes(this, {{ $session->id }})">+ catatan</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($so->customer->phone_number))
                                    <span class="copy-phone" data-phone="{{ $so->customer->phone_number }}" title="Tap to copy" style="cursor:pointer;font-size:0.82rem;color:#3b82f6;user-select:none;">{{ $so->customer->phone_number }}</span>
                                @else
                                    <span style="color:#94a3b8">—</span>
                                @endif
                            </td>
                            <td><span class="admin-pill">{{ $so->creator?->name ? Str::limit($so->creator->name, 8) : '—' }}</span></td>
                            <td>
                                <span class="status-pill status-{{ $session->status }}">{{ getStatusLabel($session->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-4">Tidak ada pekerjaan untuk tanggal ini.</td></tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        @endif

        {{-- Cancelled sessions (collapsed) --}}
        @if($cancelledSessions->count() > 0)
            <div class="cancelled-section">
                <a class="text-muted small" data-bs-toggle="collapse" href="#cancelledPanel" role="button">
                    {{ $cancelledSessions->count() }} cancelled sessions
                </a>
                <div class="collapse" id="cancelledPanel">
                    @foreach($cancelledSessions as $session)
                        @php $so = $session->serviceOrder; @endphp
                        @include('pages.planner._session_row', ['session' => $session, 'showStaffCol' => true, 'viewMode' => $viewMode])
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
const isMobile = window.innerWidth <= 768;

function fetchJson(url, options = {}) {
    return fetch(url, {
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
            ...options.headers,
        },
        ...options,
    }).then(r => {
        if (!r.ok) throw new Error('Request failed: ' + r.status);
        return r.json();
    });
}

function createInlineEditWrapper(input, onSave, onCancel) {
    if (!isMobile) return input;

    const wrapper = document.createElement('div');
    wrapper.className = 'inline-edit-wrapper';
    wrapper.style.cssText = 'display:flex;align-items:center;gap:4px;width:100%';

    const btnSave = document.createElement('button');
    btnSave.type = 'button';
    btnSave.className = 'btn btn-sm btn-success';
    btnSave.style.cssText = 'padding:2px 8px;font-size:0.75rem;min-height:28px;flex-shrink:0';
    btnSave.textContent = '✓';
    btnSave.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        onSave();
    });

    const btnCancel = document.createElement('button');
    btnCancel.type = 'button';
    btnCancel.className = 'btn btn-sm btn-outline-secondary';
    btnCancel.style.cssText = 'padding:2px 8px;font-size:0.75rem;min-height:28px;flex-shrink:0';
    btnCancel.textContent = '✕';
    btnCancel.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        onCancel();
    });

    input.style.flex = '1';
    input.style.minWidth = '0';

    wrapper.appendChild(input);
    wrapper.appendChild(btnSave);
    wrapper.appendChild(btnCancel);

    return wrapper;
}

// Inline edit: work_time
function editTime(el, soId) {
    const current = el.dataset.value || '';

    if (isMobile) {
        Swal.fire({
            title: 'Edit Jam',
            input: 'text',
            inputValue: current,
            inputPlaceholder: 'HH:MM (contoh: 09:00)',
            inputAttributes: {
                type: 'time',
                style: 'font-size: 18px; text-align: center;'
            },
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
        }).then((result) => {
            if (result.isConfirmed) {
                const val = result.value || '';
                fetchJson(`/planner/session/${soId}/update-field`, {
                    method: 'POST',
                    body: JSON.stringify({ field: 'jam', value: val }),
                }).then(data => {
                    if (data.success) {
                        el.textContent = val || '—';
                        el.dataset.value = val;
                        Swal.fire({ icon: 'success', title: 'Tersimpan', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menyimpan', timer: 3000, showConfirmButton: false });
                    }
                }).catch(err => {
                    console.error(err);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan. Coba lagi.', timer: 3000, showConfirmButton: false });
                });
            }
        });
    } else {
        const input = document.createElement('input');
        input.type = 'time';
        input.className = 'inline-edit-input';
        input.value = current;
        el.replaceWith(input);
        input.focus();

        function save() {
            const val = input.value;
            fetchJson(`/planner/session/${soId}/update-field`, {
                method: 'POST',
                body: JSON.stringify({ field: 'jam', value: val }),
            }).then(data => {
                if (data.success) {
                    const span = document.createElement('span');
                    span.className = 'inline-edit';
                    span.setAttribute('onclick', `editTime(this, ${soId})`);
                    span.dataset.value = val;
                    span.textContent = val || '—';
                    input.replaceWith(span);
                    Swal.fire({ icon: 'success', title: 'Tersimpan', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menyimpan', timer: 3000, showConfirmButton: false });
                }
            }).catch(err => {
                console.error(err);
                const span = document.createElement('span');
                span.className = 'inline-edit';
                span.setAttribute('onclick', `editTime(this, ${soId})`);
                span.dataset.value = current;
                span.textContent = current || '—';
                input.replaceWith(span);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan. Coba lagi.', timer: 3000, showConfirmButton: false });
            });
        }

        input.addEventListener('blur', save);
        input.addEventListener('keydown', e => { if (e.key === 'Enter') save(); });
    }
}

// Inline edit: notes
function editNotes(el, sessionId) {
    const current = el.dataset.value || '';
    
    // Determine if we're clicking the edit button or the notes text itself
    const isEditButton = el.classList.contains('notes-edit-btn');
    let notesElement = isEditButton ? el.parentElement.querySelector('.notes-text') : el;

    if (isMobile) {
        Swal.fire({
            title: 'Edit Catatan',
            input: 'textarea',
            inputValue: current,
            inputPlaceholder: 'Tambah catatan...',
            inputAttributes: {
                style: 'font-size: 16px; min-height: 80px;'
            },
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
        }).then((result) => {
            if (result.isConfirmed) {
                const val = result.value || '';
                fetchJson(`/planner/session/${sessionId}/update-field`, {
                    method: 'POST',
                    body: JSON.stringify({ field: 'staff_notes', value: val }),
                }).then(data => {
                    if (data.success) {
                        updateNotesDisplay(notesElement, val, sessionId);
                        Swal.fire({ icon: 'success', title: 'Tersimpan', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menyimpan', timer: 3000, showConfirmButton: false });
                    }
                }).catch(err => {
                    console.error(err);
                    updateNotesDisplay(notesElement, current, sessionId);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan. Coba lagi.', timer: 3000, showConfirmButton: false });
                });
            }
        });
    } else {
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'inline-edit-input';
        input.value = current;
        input.placeholder = 'Tambah catatan...';
        
        // For edit button, hide the notes element temporarily
        if (isEditButton) {
            notesElement.style.display = 'none';
            input.style.marginLeft = '4px';
            el.parentElement.insertBefore(input, el);
        } else {
            el.replaceWith(input);
        }
        input.focus();

        function save() {
            const val = input.value;
            fetchJson(`/planner/session/${sessionId}/update-field`, {
                method: 'POST',
                body: JSON.stringify({ field: 'staff_notes', value: val }),
            }).then(data => {
                if (data.success) {
                    updateNotesDisplay(notesElement, val, sessionId);
                    input.remove();
                    Swal.fire({ icon: 'success', title: 'Tersimpan', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menyimpan', timer: 3000, showConfirmButton: false });
                }
            }).catch(err => {
                console.error(err);
                updateNotesDisplay(notesElement, current, sessionId);
                input.remove();
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan. Coba lagi.', timer: 3000, showConfirmButton: false });
            });
        }

        input.addEventListener('blur', save);
        input.addEventListener('keydown', e => { if (e.key === 'Enter') save(); });
    }
}

// Helper function to update notes display
function updateNotesDisplay(el, val, sessionId) {
    if (!el) return;
    
    if (val) {
        el.textContent = val.length > 40 ? val.substring(0, 40) + '…' : val;
        el.setAttribute('data-bs-content', val);
        el.style.display = '';
        el.style.color = '';
        
        // Reinitialize popover if Bootstrap is available
        if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
            const existingPopover = bootstrap.Popover.getInstance(el);
            if (existingPopover) existingPopover.dispose();
            new bootstrap.Popover(el, { html: true, trigger: 'click', placement: 'right' });
        }
    } else {
        // Replace with empty notes + edit button
        const container = el.parentElement;
        if (container && container.classList.contains('notes-text-container')) {
            container.outerHTML = `<div class="notes-text inline-edit" data-value="" data-full="" onclick="editNotes(this, ${sessionId})" style="color:#cbd5e1">+ catatan</div>`;
            return;
        }
        el.textContent = '—';
        el.style.color = 'var(--text-secondary)';
    }
    
    // Update data attributes if element still exists
    if (el.dataset) {
        el.dataset.value = val;
        el.dataset.full = val;
    }
}

// Inline edit: lokasi (address location name)
function editLokasi(el, soId, addressId) {
    if (!addressId) return;
    const current = el.dataset.value || '';

    if (isMobile) {
        Swal.fire({
            title: 'Edit Lokasi',
            input: 'text',
            inputValue: current,
            inputPlaceholder: 'Contoh: Cengkareng, Serpong, Kelapa Gading...',
            inputAttributes: {
                maxlength: '100',
                style: 'font-size: 16px;'
            },
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#16a34a',
        }).then((result) => {
            if (result.isConfirmed) {
                const val = result.value.trim();
                const saveValue = val === '' ? null : val;
                fetchJson(`/planner/${soId}/update-lokasi`, {
                    method: 'POST',
                    body: JSON.stringify({ lokasi: saveValue, address_id: addressId }),
                }).then(data => {
                    if (data.success) {
                        const newLokasi = data.lokasi;

                        // Update THIS element
                        el.className = 'lokasi-badge inline-edit' + (newLokasi ? '' : ' lokasi-badge-empty');
                        el.setAttribute('onclick', `editLokasi(this, ${soId}, ${addressId})`);
                        el.dataset.value = newLokasi || '';
                        el.dataset.addressId = addressId;
                        el.textContent = newLokasi || 'Lokasi?';

                        // Update ALL other badges that share the same address_id
                        document.querySelectorAll(`.lokasi-badge[data-address-id="${addressId}"]`).forEach(badge => {
                            if (badge === el) return;
                            badge.dataset.value = newLokasi || '';
                            badge.textContent = newLokasi || 'Lokasi?';
                            badge.className = 'lokasi-badge inline-edit' + (newLokasi ? '' : ' lokasi-badge-empty');
                            badge.setAttribute('onclick', `editLokasi(this, ${soId}, ${addressId})`);
                        });

                        Swal.fire({ icon: 'success', title: 'Tersimpan', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menyimpan', timer: 3000, showConfirmButton: false });
                    }
                }).catch(err => {
                    console.error(err);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan. Coba lagi.', timer: 3000, showConfirmButton: false });
                });
            }
        });
    } else {
        // Desktop: inline input (existing behavior)
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'inline-edit-input';
        input.value = current;
        input.placeholder = 'Lokasi...';
        input.maxLength = 100;
        el.replaceWith(input);
        input.focus();
        input.select();

        function save() {
            const val = input.value.trim();
            const saveValue = val === '' ? null : val;
            fetchJson(`/planner/${soId}/update-lokasi`, {
                method: 'POST',
                body: JSON.stringify({ lokasi: saveValue, address_id: addressId }),
            }).then(data => {
                if (data.success) {
                    const newLokasi = data.lokasi;

                    // Replace input with new badge in current row
                    const newBadge = document.createElement('span');
                    newBadge.className = 'lokasi-badge inline-edit' + (newLokasi ? '' : ' lokasi-badge-empty');
                    newBadge.setAttribute('onclick', `editLokasi(this, ${soId}, ${addressId})`);
                    newBadge.dataset.value = newLokasi || '';
                    newBadge.dataset.addressId = addressId;
                    newBadge.textContent = newLokasi || 'Lokasi?';
                    input.replaceWith(newBadge);

                    // Update ALL other badges that share the same address_id
                    document.querySelectorAll(`.lokasi-badge[data-address-id="${addressId}"]`).forEach(badge => {
                        if (badge === newBadge) return;
                        badge.dataset.value = newLokasi || '';
                        badge.textContent = newLokasi || 'Lokasi?';
                        if (newLokasi) {
                            badge.classList.remove('lokasi-badge-empty');
                        } else {
                            badge.classList.add('lokasi-badge-empty');
                        }
                    });

                    Swal.fire({ icon: 'success', title: 'Tersimpan', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menyimpan', timer: 3000, showConfirmButton: false });
                }
            }).catch(err => {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan. Coba lagi.', timer: 3000, showConfirmButton: false });
                input.replaceWith(el);
            });
        }

        function cancel() {
            input.replaceWith(el);
        }

        input.addEventListener('blur', save);
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); save(); }
            if (e.key === 'Escape') cancel();
        });
    }
}
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
        Swal.fire({ icon: 'warning', title: 'Pilih staff', text: 'Pilih minimal 1 staff', timer: 3000, showConfirmButton: false });
        return;
    }
    fetchJson(`/planner/session/${soId}/update-staff`, {
        method: 'POST',
        body: JSON.stringify({ staff: selected }),
    }).then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('staffModal')).hide();

            const jobRow = document.querySelector(`[data-session-id="${soId}"]`);
            if (jobRow) {
                const staffSpan = jobRow.querySelector('.inline-edit');
                if (staffSpan) {
                    const names = data.staff_names || (data.staff && data.staff.map ? data.staff.map(s => s.name).join(', ') : '') || '—';
                    const ids = (data.staff_ids || (data.staff && data.staff.map ? data.staff.map(s => s.id) : []) || []).join(',');
                    staffSpan.textContent = names;
                    staffSpan.dataset.staffIds = ids;
                }
            }

            Swal.fire({ icon: 'success', title: 'Staff diperbarui', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal menyimpan', timer: 3000, showConfirmButton: false });
        }
    }).catch(err => {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan. Coba lagi.', timer: 3000, showConfirmButton: false });
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

            const chip = btn.closest('.staff-chip') || btn;
            if (data.is_off) {
                chip.classList.add('staff-off');
                chip.classList.remove('staff-free', 'staff-assigned');
            } else {
                chip.classList.remove('staff-off');
                chip.classList.add('staff-free');
            }

            Swal.fire({ icon: 'success', title: data.is_off ? 'Staff OFF' : 'Staff Available', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal toggle', timer: 3000, showConfirmButton: false });
        }
    }).catch(err => {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan. Coba lagi.', timer: 3000, showConfirmButton: false });
    });
}
function toggleStaffOffDirectly(staffId, date) {
    fetchJson('/planner/toggle-staff-off', {
        method: 'POST',
        body: JSON.stringify({ staff_id: staffId, date: date }),
    }).then(data => {
        if (data.success) {
            // Find the chip for this staff and update it
            const chips = document.querySelectorAll('.staff-chip');
            chips.forEach(chip => {
                const chipStaffId = chip.dataset.staffId;
                if (chipStaffId == staffId) {
                    chip.classList.toggle('is-off', data.is_off);

                    const dot = chip.querySelector('.staff-chip-status-dot');
                    const svg = chip.querySelector('svg.text-danger');
                    if (data.is_off) {
                        if (dot) dot.remove();
                        if (!svg) {
                            const svgEl = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                            svgEl.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
                            svgEl.setAttribute('width', '12');
                            svgEl.setAttribute('height', '12');
                            svgEl.setAttribute('viewBox', '0 0 24 24');
                            svgEl.setAttribute('fill', 'none');
                            svgEl.setAttribute('stroke', 'currentColor');
                            svgEl.setAttribute('stroke-width', '3');
                            svgEl.setAttribute('stroke-linecap', 'round');
                            svgEl.setAttribute('stroke-linejoin', 'round');
                            svgEl.classList.add('text-danger');
                            svgEl.innerHTML = '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>';
                            chip.insertBefore(svgEl, chip.firstChild);
                        }
                    } else {
                        if (svg) svg.remove();
                        if (!dot) {
                            const dotEl = document.createElement('span');
                            dotEl.className = 'staff-chip-status-dot dot-available';
                            chip.insertBefore(dotEl, chip.firstChild);
                        }
                    }
                }
            });

            Swal.fire({ icon: 'success', title: data.is_off ? 'Staff OFF' : 'Staff Available', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || 'Gagal toggle', timer: 3000, showConfirmButton: false });
        }
    }).catch(err => {
        console.error(err);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan jaringan. Coba lagi.', timer: 3000, showConfirmButton: false });
    });
}

// Initialize Bootstrap popovers for pekerjaan badges
document.addEventListener('DOMContentLoaded', function() {
    // Defer to next macrotask so Vite module script (app.js) finishes first.
    // <script type="module"> is deferred and runs AFTER DOMContentLoaded, so
    // window.bootstrap / window.Swal / window.$ are NOT available yet in the
    // DOMContentLoaded handler. setTimeout pushes this to the next macrotask,
    // after the module script has executed.
    setTimeout(function() {
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

        // Tap to copy phone number
        $(document).on('click', '.copy-phone', function () {
            const phone = $(this).data('phone');
            if (!phone) return;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(phone).then(() => {
                    showPhoneCopyFeedback($(this));
                }).catch(() => {
                    fallbackPhoneCopy(phone, $(this));
                });
            } else {
                fallbackPhoneCopy(phone, $(this));
            }
        });

        function showPhoneCopyFeedback($el) {
            const original = $el.text();
            $el.text('Copied!').css('color', 'var(--tblr-success)');
            setTimeout(() => {
                $el.text(original).css('color', '#3b82f6');
            }, 1500);
        }

        function fallbackPhoneCopy(text, $el) {
            const $temp = $('<input>');
            $('body').append($temp);
            $temp.val(text).select();
            try {
                document.execCommand('copy');
                showPhoneCopyFeedback($el);
            } catch (e) {}
            $temp.remove();
        }
    }, 0);
});

// Notes: single tap = popover (delayed), double tap = edit
var _notesClickTimer = null;
var _notesPopoverTimeout = null;
document.addEventListener('click', function(e) {
    var el = e.target.closest('.notes-text');
    if (!el) return;
    e.preventDefault();

    var now = Date.now();

    if (_notesClickTimer && (now - _notesClickTimer) < 400) {
        // Double tap → cancel pending popover, open editor
        clearTimeout(_notesPopoverTimeout);
        _notesClickTimer = null;
        hideNotesPopover();
        editNotes(el, parseInt(el.closest('[data-session-id]').dataset.sessionId));
    } else {
        // Possible single tap — wait 400ms before showing popover
        _notesClickTimer = now;
        var full = el.dataset.full || el.dataset.value || '';
        if (!full) return;

        _notesPopoverTimeout = setTimeout(function() {
            showNotesPopover(e.clientX, e.clientY, full);
            _notesClickTimer = null;
        }, 400);
    }
});

function showNotesPopover(clickX, clickY, text) {
    hideNotesPopover();
    var pop = document.createElement('div');
    pop.id = 'notes-popover';
    pop.textContent = text;
    pop.style.cssText = 'position:fixed;background:#1e293b;color:#f8fafc;padding:6px 10px;border-radius:6px;font-size:0.8rem;max-width:260px;box-shadow:0 4px 12px rgba(0,0,0,0.2);white-space:pre-wrap;word-break:break-word;pointer-events:none;z-index:9999;';

    var left = Math.min(clickX, window.innerWidth - 270);
    var top = clickY + 10;
    if (top + 80 > window.innerHeight) top = clickY - 90;
    pop.style.left = left + 'px';
    pop.style.top = top + 'px';
    document.body.appendChild(pop);
    setTimeout(hideNotesPopover, 2000);
}

function hideNotesPopover() {
    var existing = document.getElementById('notes-popover');
    if (existing) existing.remove();
}

// Date picker — tap date heading to open native date picker
(function() {
    var dateHeading = document.getElementById('planner-date-heading');
    var dateInput = document.getElementById('planner-date-picker');
    if (!dateHeading || !dateInput) return;

    dateHeading.addEventListener('click', function() {
        // showPicker() works in Chrome 125+, Safari 17.4+, Firefox 126+
        if (typeof dateInput.showPicker === 'function') {
            dateInput.showPicker();
        } else {
            // Fallback for older browsers
            dateInput.focus();
            dateInput.click();
        }
    });

    dateInput.addEventListener('change', function() {
        var selectedDate = this.value; // YYYY-MM-DD
        if (!selectedDate) return;

        // Build URL matching the existing < > arrow pattern
        var currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('date', selectedDate);
        window.location.href = currentUrl.toString();
    });
})();

</script>
@endpush
