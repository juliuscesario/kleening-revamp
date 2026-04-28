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
    .planner-date-nav { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .planner-date-label { font-size: 1.15rem; font-weight: 600; min-width: 180px; text-align: center; }
    .planner-area-tabs { display: flex; gap: .25rem; flex-wrap: wrap; }
    .planner-area-tab { padding: .35rem .75rem; border-radius: .375rem; font-size: .8rem; font-weight: 500; cursor: pointer; border: 1px solid #dee2e6; background: #fff; color: #666; text-decoration: none; }
    .planner-area-tab.active { background: #1e293b; color: #fff; border-color: #1e293b; }
    .planner-view-toggle .btn { padding: .3rem .6rem; font-size: .75rem; }
    .summary-pills { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .summary-pill { background: #f1f5f9; border-radius: .5rem; padding: .4rem .75rem; font-size: .8rem; }
    .summary-pill strong { font-size: 1rem; }

    /* Off day badges */
    .off-badge { font-size: .7rem; background: #fee2e2; color: #991b1b; padding: .15rem .5rem; border-radius: 999px; font-weight: 500; display: inline-block; margin: .1rem; }

    /* Unassigned section */
    .unassigned-section { background: #fef2f2; border: 1px solid #fecaca; border-radius: .5rem; padding: .75rem; margin-bottom: 1rem; }
    .unassigned-title { font-size: .85rem; font-weight: 600; color: #991b1b; margin-bottom: .5rem; }

    /* Staff group header */
    .staff-group-header { display: flex; align-items: center; gap: .5rem; padding: .5rem 0; border-bottom: 2px solid #e2e8f0; margin-top: 1rem; margin-bottom: .5rem; flex-wrap: wrap; }
    .staff-group-name { font-weight: 600; font-size: .95rem; }
    .staff-group-count { font-size: .75rem; color: #94a3b8; }
    .staff-group-route { font-size: .75rem; color: #64748b; margin-left: auto; }

    /* Job row */
    .job-row { display: flex; align-items: flex-start; gap: .5rem; padding: .5rem .25rem; border-bottom: 1px solid #f1f5f9; font-size: .85rem; }
    .job-row:hover { background: #f8fafc; }
    .job-time { font-weight: 600; min-width: 36px; flex-shrink: 0; }
    .job-main { flex: 1; min-width: 0; }
    .job-name { font-weight: 500; }
    .job-lokasi { font-size: .78rem; color: #64748b; }
    .job-notes-preview { font-size: .75rem; color: #94a3b8; font-style: italic; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 200px; }
    .job-cats { display: flex; gap: .2rem; flex-wrap: wrap; flex-shrink: 0; }
    .job-staff-col { font-size: .78rem; color: #64748b; min-width: 70px; flex-shrink: 0; }
    .job-status-col { flex-shrink: 0; }
    .job-actions { flex-shrink: 0; }

    /* Category badges */
    .cat-badge { font-size: .65rem; padding: .1rem .4rem; border-radius: .25rem; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
    .cat-hv, .cat-hydrovacuum { background: #dbeafe; color: #1e40af; }
    .cat-cc, .cat-premiumwash, .cat-premium { background: #ede9fe; color: #5b21b6; }
    .cat-gc, .cat-generalcleaning, .cat-general { background: #dcfce7; color: #166534; }
    .cat-dc, .cat-deepcleaning, .cat-deep { background: #fef3c7; color: #92400e; }
    .cat-cid, .cat-carinterior, .cat-car { background: #ffe4e6; color: #9f1239; }
    .cat-poles { background: #f3e8ff; color: #6b21a8; }
    .cat-survey { background: #e0f2fe; color: #075985; }
    .cat-default { background: #f1f5f9; color: #475569; }

    /* Status badges */
    .status-badge { font-size: .65rem; padding: .15rem .5rem; border-radius: 999px; font-weight: 600; white-space: nowrap; }
    .status-booked { background: #ede9fe; color: #5b21b6; }
    .status-proses { background: #fef3c7; color: #92400e; }
    .status-done { background: #dcfce7; color: #166534; }
    .status-invoiced { background: #fce7f3; color: #9d174d; }
    .status-sent { background: #d1fae5; color: #065f46; }
    .status-overdue { background: #dbeafe; color: #1e40af; }
    .status-paid { background: #ccfbf1; color: #115e59; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    /* Admin badge */
    .admin-badge { font-size: .65rem; padding: .1rem .4rem; border-radius: .25rem; font-weight: 500; }

    /* Inline edit */
    .inline-edit { cursor: pointer; border-bottom: 1px dashed #cbd5e1; }
    .inline-edit:hover { background: #f1f5f9; }
    .inline-edit-input { font-size: .85rem; padding: .15rem .3rem; border: 1px solid #93c5fd; border-radius: .25rem; width: 100%; }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .job-row { flex-wrap: wrap; }
        .job-staff-col { display: none; }
        .staff-group-route { margin-left: 0; width: 100%; }
        .job-notes-preview { max-width: 140px; }
        .planner-date-label { min-width: 140px; font-size: 1rem; }
        .desktop-only { display: none !important; }
    }
    @media (min-width: 769px) {
        .mobile-only { display: none !important; }
    }

    /* Staff off toggle */
    .staff-off-list { display: flex; flex-wrap: wrap; gap: .25rem; align-items: center; }
    .staff-off-toggle { cursor: pointer; font-size: .75rem; padding: .2rem .5rem; border-radius: 999px; border: 1px solid #e2e8f0; background: #fff; color: #64748b; transition: all .15s; }
    .staff-off-toggle:hover { border-color: #f87171; }
    .staff-off-toggle.is-off { background: #fee2e2; color: #991b1b; border-color: #fecaca; }

    /* Cancelled section */
    .cancelled-section { opacity: .5; margin-top: 1.5rem; }
    .cancelled-section:hover { opacity: .8; }

    /* Quick WA link */
    .wa-link { color: #22c55e; text-decoration: none; font-size: .8rem; }
    .wa-link:hover { text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="page-body">
    <div class="container-xl">
        {{-- Top bar: Date nav + Area tabs + View toggle --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div class="planner-date-nav">
                <a href="{{ route('web.planner.index', ['date' => $prevDate, 'area_id' => $areaId, 'view' => $viewMode]) }}" class="btn btn-outline-secondary btn-sm">&lsaquo;</a>
                <div class="planner-date-label">
                    {{ $carbonDate->translatedFormat('l, j M Y') }}
                </div>
                <a href="{{ route('web.planner.index', ['date' => $nextDate, 'area_id' => $areaId, 'view' => $viewMode]) }}" class="btn btn-outline-secondary btn-sm">&rsaquo;</a>
                @if($date !== $today)
                    <a href="{{ route('web.planner.index', ['area_id' => $areaId, 'view' => $viewMode]) }}" class="btn btn-outline-primary btn-sm">Hari ini</a>
                @endif
            </div>

            <div class="d-flex gap-2 align-items-center flex-wrap">
                <div class="planner-area-tabs">
                    <a href="{{ route('web.planner.index', ['date' => $date, 'view' => $viewMode]) }}" class="planner-area-tab {{ !$areaId ? 'active' : '' }}">Semua</a>
                    @foreach($areas as $area)
                        <a href="{{ route('web.planner.index', ['date' => $date, 'area_id' => $area->id, 'view' => $viewMode]) }}" class="planner-area-tab {{ $areaId == $area->id ? 'active' : '' }}">{{ $area->name }}</a>
                    @endforeach
                </div>

                <div class="planner-view-toggle btn-group">
                    <a href="{{ route('web.planner.index', ['date' => $date, 'area_id' => $areaId, 'view' => 'staff']) }}" class="btn {{ $viewMode === 'staff' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0-3-3.85"/></svg>
                        Staff
                    </a>
                    <a href="{{ route('web.planner.index', ['date' => $date, 'area_id' => $areaId, 'view' => 'list']) }}" class="btn {{ $viewMode === 'list' ? 'btn-primary' : 'btn-outline-secondary' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6h11"/><path d="M9 12h11"/><path d="M9 18h11"/><path d="M5 6v.01"/><path d="M5 12v.01"/><path d="M5 18v.01"/></svg>
                        List
                    </a>
                </div>
            </div>
        </div>

        {{-- Summary pills --}}
        <div class="summary-pills">
            <div class="summary-pill"><strong>{{ $totalJobs }}</strong> jobs</div>
            <div class="summary-pill"><strong>{{ $activeStaffCount }}</strong> staff aktif</div>
            <div class="summary-pill" style="{{ $unassignedJobs->count() > 0 ? 'background:#fef2f2;color:#991b1b' : '' }}"><strong>{{ $unassignedJobs->count() }}</strong> unassigned</div>
            @if($offDays->count() > 0)
                <div class="summary-pill">
                    OFF:
                    @foreach($offDays as $od)
                        <span class="off-badge">{{ $od->staff->name }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Staff off day management (collapsible) --}}
        <div class="mb-3">
            <a class="text-muted small" data-bs-toggle="collapse" href="#staffOffPanel" role="button" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4-4h3.5"/><path d="M22 22l-5-5"/><path d="M17 22l5-5"/></svg>
                Kelola OFF staff hari ini
            </a>
            <div class="collapse {{ $offDays->count() > 0 ? 'show' : '' }}" id="staffOffPanel">
                <div class="card card-body mt-2 p-2">
                    <div class="staff-off-list">
                        @foreach($allStaff as $s)
                            <button type="button"
                                class="staff-off-toggle {{ $offDays->has($s->id) ? 'is-off' : '' }}"
                                data-staff-id="{{ $s->id }}"
                                data-date="{{ $date }}"
                                onclick="toggleStaffOff(this)">
                                {{ $s->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Unassigned jobs --}}
        @if($unassignedJobs->count() > 0)
            <div class="unassigned-section">
                <div class="unassigned-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"/><path d="M12 17v.01"/><path d="M5 19h14a2 2 0 0 0 1.84-2.75l-7.1-12.25a2 2 0 0 0-3.5 0l-7.1 12.25a2 2 0 0 0 1.84 2.75"/></svg>
                    {{ $unassignedJobs->count() }} job belum ada staff
                </div>
                @foreach($unassignedJobs as $so)
                    @include('pages.planner._job_row', ['so' => $so, 'showStaffCol' => true])
                @endforeach
            </div>
        @endif

        {{-- Main content: Staff view or List view --}}
        @if($viewMode === 'staff')
            {{-- STAFF VIEW --}}
            @forelse($jobsByStaff as $staffId => $group)
                <div class="staff-group-header">
                    <div class="staff-group-name">{{ $group['staff']->name }}</div>
                    <div class="staff-group-count">{{ $group['jobs']->count() }} {{ $group['jobs']->count() === 1 ? 'job' : 'jobs' }}</div>
                    @if($offDays->has($staffId))
                        <span class="off-badge">OFF</span>
                    @endif
                    <div class="staff-group-route desktop-only">{{ $group['route'] }}</div>
                </div>
                <div class="staff-group-route mobile-only mb-2" style="font-size:.75rem;color:#64748b;">{{ $group['route'] }}</div>
                @foreach($group['jobs'] as $so)
                    @include('pages.planner._job_row', ['so' => $so, 'showStaffCol' => false])
                @endforeach
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
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-vcenter table-sm mb-0">
                        <thead>
                            <tr>
                                <th style="width:50px">Jam</th>
                                <th>Customer</th>
                                <th>Lokasi</th>
                                <th>Staff</th>
                                <th>Pekerjaan</th>
                                <th>Notes</th>
                                <th>Admin</th>
                                <th style="width:80px">Status</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceOrders->where('status', '!=', 'cancelled')->sortBy(fn($s) => $s->work_time ?? '23:59:59') as $so)
                                <tr data-so-id="{{ $so->id }}">
                                    <td>
                                        <span class="inline-edit" onclick="editTime(this, {{ $so->id }})" data-value="{{ $so->work_time ? \Carbon\Carbon::createFromFormat('H:i:s', $so->work_time)->format('H:i') : '' }}">
                                            <strong>{{ $so->work_time ? \Carbon\Carbon::createFromFormat('H:i:s', $so->work_time)->format('H:i') : '—' }}</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('web.service-orders.show', $so) }}" class="fw-medium text-reset">{{ $so->customer?->name ?? '—' }}</a>
                                        <br><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $so->customer?->phone_number ?? '') }}" target="_blank" class="wa-link">WA</a>
                                    </td>
                                    <td><span class="text-muted">{{ $so->address?->lokasi ?? ($so->address?->label ?? '—') }}</span></td>
                                    <td>
                                        <span class="inline-edit" onclick="editStaff(this, {{ $so->id }})" data-staff-ids="{{ $so->staff->pluck('id')->implode(',') }}">
                                            {{ $so->staff->pluck('name')->implode(', ') ?: '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        @foreach($so->items as $item)
                                            <span class="cat-badge {{ getCategoryBadgeClass($item->service?->category?->name) }}">{{ getCategoryShortCode($item->service?->category?->name) }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <span class="inline-edit job-notes-preview" style="max-width:none" onclick="editNotes(this, {{ $so->id }})" data-value="{{ $so->work_notes }}">
                                            {{ $so->work_notes ?: '—' }}
                                        </span>
                                    </td>
                                    <td><span class="admin-badge" style="background:#f1f5f9">{{ $so->creator?->name ? Str::limit($so->creator->name, 8) : '—' }}</span></td>
                                    <td><span class="status-badge status-{{ $so->lifecycle_status }}">{{ getStatusLabel($so->lifecycle_status) }}</span></td>
                                    <td>
                                        <a href="{{ route('web.service-orders.show', $so) }}" class="btn btn-sm btn-ghost-secondary p-1" title="Detail">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5h-2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-12a2 2 0 0 0-2-2h-2"/><path d="M9 3m0 2a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v0a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada pekerjaan untuk tanggal ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
            body: JSON.stringify({ field: 'work_notes', value: val }),
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
</script>
@endpush
