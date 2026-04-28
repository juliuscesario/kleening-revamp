<div class="job-row" data-so-id="{{ $so->id }}">
    <div class="job-time">
        <span class="inline-edit" onclick="editTime(this, {{ $so->id }})" data-value="{{ $so->work_time ? \Carbon\Carbon::createFromFormat('H:i:s', $so->work_time)->format('H:i') : '' }}">
            {{ $so->work_time ? \Carbon\Carbon::createFromFormat('H:i:s', $so->work_time)->format('H:i') : '—' }}
        </span>
    </div>
    <div class="job-main">
        <div>
            <a href="{{ route('web.service-orders.show', $so) }}" class="job-name text-reset">{{ $so->customer?->name ?? '—' }}</a>
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $so->customer?->phone_number ?? '') }}" target="_blank" class="wa-link ms-1" title="WhatsApp">WA</a>
        </div>
        <div class="job-lokasi">{{ $so->address?->lokasi ?? ($so->address?->label ?? '—') }}</div>
        @if($so->work_notes)
            <div class="job-notes-preview inline-edit" onclick="editNotes(this, {{ $so->id }})" data-value="{{ $so->work_notes }}">{{ $so->work_notes }}</div>
        @else
            <div class="job-notes-preview inline-edit" onclick="editNotes(this, {{ $so->id }})" data-value="" style="color:#cbd5e1">+ catatan</div>
        @endif
    </div>
    <div class="job-cats">
        @foreach($so->items as $item)
            <span class="cat-badge {{ getCategoryBadgeClass($item->service?->category?->name) }}">{{ getCategoryShortCode($item->service?->category?->name) }}</span>
        @endforeach
    </div>
    @if($showStaffCol ?? true)
        <div class="job-staff-col">
            <span class="inline-edit" onclick="editStaff(this, {{ $so->id }})" data-staff-ids="{{ $so->staff->pluck('id')->implode(',') }}">
                {{ $so->staff->pluck('name')->implode(', ') ?: '—' }}
            </span>
        </div>
    @endif
    <div class="job-status-col">
        <span class="status-badge status-{{ $so->lifecycle_status }}">{{ getStatusLabel($so->lifecycle_status) }}</span>
    </div>
    <div class="job-actions">
        <a href="{{ route('web.service-orders.show', $so) }}" class="btn btn-sm btn-ghost-secondary p-1" title="Detail">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5h-2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-12a2 2 0 0 0-2-2h-2"/><path d="M9 3m0 2a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v0a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z"/></svg>
        </a>
    </div>
</div>
