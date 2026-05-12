<div class="job-row" data-session-id="{{ $session->id }}" data-so-id="{{ $session->serviceOrder->id }}">
    <div class="job-time">
        <span class="inline-edit fw-bold text-dark" onclick="editTime(this, {{ $session->id }})" data-value="{{ $session->jam ? \Carbon\Carbon::parse($session->jam)->format('H:i') : '' }}">
            {{ $session->jam ? \Carbon\Carbon::parse($session->jam)->format('H:i') : '—' }}
        </span>
    </div>
    <div class="job-main">
        <div class="d-flex align-items-center gap-1 flex-wrap">
            <a href="{{ route('web.service-orders.show', $session->serviceOrder) }}" class="job-name-link">{{ $session->serviceOrder->customer?->name ?? '—' }}</a>
            {{-- Session number badge --}}
            @if($session->serviceOrder->is_multi_session)
                @php $totalSessions = $session->serviceOrder->sessions()->count(); @endphp
                <span class="session-num-badge d-none d-sm-inline">S{{ $session->session_number }}/{{ $totalSessions }}</span>
                <span class="session-num-badge-mobile d-sm-none">S{{ $session->session_number }}/{{ $totalSessions }}</span>
            @endif
            {{-- Session type badge --}}
            @if($session->type !== 'kerja')
                @php
                    $typeBadgeClass = match($session->type) {
                        'pickup' => 'bg-warning',
                        'delivery' => 'bg-success',
                        'survey' => 'bg-purple-lt',
                        'workshop' => 'bg-orange-lt',
                        'rework' => 'bg-red-lt',
                        default => 'bg-secondary',
                    };
                @endphp
                <span class="session-type-badge type-{{ $session->type }} badge bg-{{ $typeBadgeClass }} text-bg-secondary">{{ strtoupper($session->type) }}</span>
            @endif
        </div>
        <div class="mt-1 d-flex align-items-center gap-2">
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $session->serviceOrder->customer?->phone_number ?? '') }}" target="_blank" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 py-0 px-2" style="font-size: 0.6rem; font-weight: 700; border-radius: 4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                WA
            </a>
            @php
                $so = $session->serviceOrder;
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
        </div>
        @if($so->staff_notes)
            <div class="notes-text-container" style="position: relative; display: inline-block;">
                <div class="notes-text" data-bs-toggle="popover" data-bs-trigger="click" data-bs-content="{{ e($so->staff_notes) }}" style="cursor: pointer;">
                    {{ Str::limit($so->staff_notes, 40, '…') }}
                </div>
                <span class="notes-edit-btn" onclick="editNotes(this, {{ $session->id }})" data-value="{{ e($so->staff_notes) }}" data-full="{{ e($so->staff_notes) }}" title="Edit catatan">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                </span>
            </div>
        @else
            <div class="notes-text inline-edit" data-value="" data-full="" onclick="editNotes(this, {{ $session->id }})" style="color:#cbd5e1">+ catatan</div>
        @endif
    </div>
    <div class="job-cats">
        @php
            $uniqueCategories = $so->items
                ->map(fn($item) => $item->service?->category?->name)
                ->filter()
                ->unique()
                ->values();
        @endphp
        @foreach($uniqueCategories as $catName)
            <span class="cat-tag cat-{{ strtolower(getCategoryShortCode($catName)) }}">{{ getCategoryShortCode($catName) }}</span>
        @endforeach
    </div>
    @if($showStaffCol ?? true)
        <div class="job-staff-col" style="width:120px; flex-shrink:0;">
            @php
                $sessionStaffNames = $session->staff->pluck('name')->implode(', ') ?: 'Unassigned';
                $sessionStaffMachines = $session->staff->filter(fn($s) => isset($activeAttendances[$s->id]) && $activeAttendances[$s->id])->map(fn($s) => $s->name . ': ' . $activeAttendances[$s->id])->join(', ');
            @endphp
            <span class="inline-edit fw-medium" style="font-size:.875rem;" onclick="editStaff(this, {{ $session->id }})" data-staff-ids="{{ $session->staff->pluck('id')->implode(',') }}" @if($sessionStaffMachines) title="{{ $sessionStaffMachines }}" @endif>
                {{ $sessionStaffNames }}
            </span>
        </div>
    @endif
    <div class="job-status-col">
        {{-- Session status (booked/proses/done/cancel) --}}
        <span class="status-pill status-{{ $session->status }}">{{ getStatusLabel($session->status) }}</span>
    </div>
    @if(($viewMode ?? 'staff') === 'list')
    <div class="job-actions">
        <a href="{{ route('web.service-orders.show', $session->serviceOrder) }}" class="btn btn-sm btn-ghost-secondary p-1" title="Detail">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5h-2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-12a2 2 0 0 0-2-2h-2"/><path d="M9 3m0 2a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v0a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z"/></svg>
        </a>
    </div>
    @endif
</div>
