<div style="display: flex; flex-direction: column; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; height: 100%;">

    {{-- Photo or placeholder — fixed 140px --}}
    <div style="height: 140px; overflow: hidden; background: #2a2a3e; border-radius: 6px 6px 0 0; @if($photo) cursor: pointer; @endif" @if($photo) onclick="openLightbox('{{ asset('storage/' . $photo->file_path) }}')" @endif>
        @if($photo)
            <img src="{{ asset('storage/' . $photo->file_path) }}"
                 style="width: 100%; height: 100%; object-fit: cover; display: block;">
        @else
            <div style="display: flex; align-items: center; justify-content: center; height: 100%; text-align: center; color: #6c757d;">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-12z" /><path d="M13 12h-4l2 -3z" /></svg>
                    <div style="font-size: 11px; margin-top: 4px;">Belum ada foto</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Meta + buttons always at bottom --}}
    <div style="padding: 10px; background: var(--tblr-card-bg, #fff); flex-shrink: 0;">
        <div style="text-align: center; margin-bottom: 6px;">
            <span class="badge bg-{{ $badgeClass }}">{{ $label }}</span>
        </div>
        @if($photo)
            <div style="font-size: 11px; color: #6c757d; margin-bottom: 8px; text-align: center; max-height: 2.4em; overflow: hidden; line-height: 1.2;">
                {{ $photo->uploader->name ?? 'Unknown' }}<br>
                {{ $photo->created_at->format('d M Y H:i') }}
            </div>
        @endif
        <div style="display: flex; gap: 8px;">
            @if($photo)
                <button class="btn btn-sm btn-outline-primary btn-replace w-50" data-type="{{ $type }}" data-so-id="{{ $serviceOrderId }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-upload" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><polyline points="7 11 12 6 17 11" /><line x1="12" y1="6" x2="12" y2="18" /></svg>
                    Ganti
                </button>
                <button class="btn btn-sm btn-outline-danger btn-delete-photo w-50" data-photo-id="{{ $photo->id }}" data-so-id="{{ $serviceOrderId }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    Hapus
                </button>
            @else
                <button class="btn btn-sm btn-primary btn-upload w-100" data-type="{{ $type }}" data-so-id="{{ $serviceOrderId }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-camera" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 7h1a2 2 0 0 0 2 -2a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1a2 2 0 0 0 2 2h1a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-9a2 2 0 0 1 2 -2z" /><circle cx="12" cy="13" r="3" /></svg>
                    Upload
                </button>
            @endif
        </div>
        <input type="file" class="d-none photo-input" accept="image/*" data-type="{{ $type }}">
    </div>

</div>
