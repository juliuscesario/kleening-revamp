<div class="job-row" data-so-id="{{ $so->id }}">
    <div class="job-time">
        <span class="inline-edit fw-bold text-dark" onclick="editTime(this, {{ $so->id }})" data-value="{{ $so->work_time ? \Carbon\Carbon::createFromFormat('H:i:s', $so->work_time)->format('H:i') : '' }}">
            {{ $so->work_time ? \Carbon\Carbon::createFromFormat('H:i:s', $so->work_time)->format('H:i') : '—' }}
        </span>
    </div>
    <div class="job-main">
        <div>
            <a href="{{ route('web.service-orders.show', $so) }}" class="job-name-link">{{ $so->customer?->name ?? '—' }}</a>
        </div>
        <div class="mt-1 d-flex align-items-center gap-2">
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $so->customer?->phone_number ?? '') }}" target="_blank" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1 py-0 px-2" style="font-size: 0.6rem; font-weight: 700; border-radius: 4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                WA
            </a>
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
        </div>
        @if($so->staff_notes)
            <div class="notes-text inline-edit" onclick="editNotes(this, {{ $so->id }})" data-value="{{ $so->staff_notes }}">{{ $so->staff_notes }}</div>
        @else
            <div class="notes-text inline-edit" onclick="editNotes(this, {{ $so->id }})" data-value="" style="color:#cbd5e1">+ catatan</div>
        @endif
    </div>
    <div class="job-cats">
        @foreach($so->items as $item)
            <span class="cat-tag cat-{{ strtolower(getCategoryShortCode($item->service?->category?->name)) }}">{{ getCategoryShortCode($item->service?->category?->name) }}</span>
        @endforeach
    </div>
    @if($showStaffCol ?? true)
        <div class="job-staff-col" style="width:120px; flex-shrink:0;">
            <span class="inline-edit fw-medium" style="font-size:.875rem;" onclick="editStaff(this, {{ $so->id }})" data-staff-ids="{{ $so->staff->pluck('id')->implode(',') }}">
                {{ $so->staff->pluck('name')->implode(', ') ?: 'Unassigned' }}
            </span>
        </div>
    @endif
    <div class="job-status-col">
        <span class="status-pill status-{{ $so->lifecycle_status }}">{{ getStatusLabel($so->lifecycle_status) }}</span>
    </div>
    <div class="job-actions">
        <a href="{{ route('web.service-orders.show', $so) }}" class="btn btn-sm btn-ghost-secondary p-1" title="Detail">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5h-2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-12a2 2 0 0 0-2-2h-2"/><path d="M9 3m0 2a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v0a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z"/></svg>
        </a>
    </div>
</div>
