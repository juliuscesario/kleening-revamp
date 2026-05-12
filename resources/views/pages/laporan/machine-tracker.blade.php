@extends('layouts.admin')

@section('title', 'Machine Tracker')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Laporan</div>
                <h2 class="page-title">Machine Tracker</h2>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Machine Selector --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-end g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Pilih Mesin</label>
                        <select id="machine-select" class="form-select">
                            <option value="">-- Pilih mesin --</option>
                            @foreach($machines as $area => $areaGroup)
                                <optgroup label="{{ $area }}">
                                    @foreach($areaGroup as $machine)
                                        <option value="{{ $machine->id }}">
                                            {{ strtoupper($machine->code) }}
                                            @if($machine->name) – {{ $machine->name }} @endif
                                            ({{ $machine->category?->name ?? '-' }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Dari Tanggal</label>
                        <input type="date" id="date-from" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Sampai Tanggal</label>
                        <input type="date" id="date-to" class="form-control">
                    </div>
                    <div class="col-auto">
                        <button id="btn-search" type="button" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-search" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="10" cy="10" r="7"/><line x1="21" y1="21" x2="15" y2="15"/>
                            </svg>
                            Cari
                        </button>
                    </div>
                    <div class="col-auto">
                        <div id="loading-spinner" class="spinner-border spinner-border-sm text-primary d-none" role="status"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Result Panel (hidden until machine selected) --}}
        <div id="tracker-result" class="d-none">

            {{-- Status Card --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title" id="machine-title">—</h3>
                    <div class="card-options">
                        <span id="machine-status-badge" class="badge"></span>
                    </div>
                </div>
                <div class="card-body">

                    {{-- ACTIVE: someone is holding it --}}
                    <div id="status-active" class="d-none">
                        <div class="alert alert-warning d-flex align-items-center gap-3 mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                            </svg>
                            <div>
                                <strong>Sedang Dibawa</strong>
                                <div class="text-muted small">Mesin ini saat ini sedang dalam perjalanan / digunakan.</div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-3">
                                <div class="subheader mb-1">Staff</div>
                                <div class="fw-bold fs-4" id="active-staff">—</div>
                            </div>
                            <div class="col-sm-3">
                                <div class="subheader mb-1">Tanggal</div>
                                <div id="active-date">—</div>
                            </div>
                            <div class="col-sm-3">
                                <div class="subheader mb-1">Jam Pergi</div>
                                <div id="active-jam-pergi">—</div>
                            </div>
                            <div class="col-sm-3">
                                <div class="subheader mb-1">Catatan</div>
                                <div id="active-catatan" class="text-muted">—</div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="subheader mb-2">Foto Pergi</div>
                            <img id="active-photo" src="" alt="Foto Pergi"
                                 class="rounded border d-none"
                                 style="height:120px;width:120px;object-fit:cover;cursor:pointer;"
                                 data-bs-toggle="modal" data-bs-target="#lightbox-modal">
                        </div>
                    </div>

                    {{-- AVAILABLE --}}
                    <div id="status-available" class="d-none">
                        <div class="alert alert-success d-flex align-items-center gap-3 mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <div>
                                <strong>Tersedia</strong>
                                <div class="text-muted small">Mesin ini tidak sedang dibawa oleh siapa pun.</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- History Table --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Pemakaian</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Staff</th>
                                    <th>Jam Pergi</th>
                                    <th>Jam Pulang</th>
                                    <th>Durasi</th>
                                    <th>Catatan</th>
                                    <th>Foto Mesin Pergi</th>
                                    <th>Foto Mesin Pulang</th>
                                </tr>
                            </thead>
                            <tbody id="history-tbody">
                                <tr id="history-empty">
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada riwayat.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>{{-- /#tracker-result --}}

    </div>
</div>

{{-- Lightbox Modal --}}
<div class="modal modal-blur fade" id="lightbox-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="lightbox-title">Foto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center" style="min-height:300px;position:relative;">
                <img id="lightbox-img" src="" alt="Photo" class="img-fluid" style="max-height:80vh;object-fit:contain;">
                <button id="lightbox-prev" class="btn btn-icon btn-floating position-absolute top-50 start-0 translate-middle-y bg-white bg-opacity-25 text-white border-0 m-3 d-none" style="z-index:10;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button id="lightbox-next" class="btn btn-icon btn-floating position-absolute top-50 end-0 translate-middle-y bg-white bg-opacity-25 text-white border-0 m-3 d-none" style="z-index:10;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Wait for jQuery to be available (it loads via Vite async)
    function waitForJQuery() {
        if (typeof jQuery === 'undefined') {
            setTimeout(waitForJQuery, 50);
            return;
        }
        const $ = jQuery;
        const lookupUrl = '{{ route("web.laporan.machine-tracker.lookup") }}';

        function doLookup() {
            const machineId = $('#machine-select').val();
            if (!machineId) {
                Swal.fire('Info', 'Pilih mesin terlebih dahulu.', 'info');
                return;
            }

            $('#loading-spinner').removeClass('d-none');
            $('#tracker-result').addClass('d-none');

            const params = { machine_id: machineId };
            const from = $('#date-from').val();
            const to   = $('#date-to').val();
            if (from) params.from = from;
            if (to)   params.to   = to;

            console.log('[MachineTracker] Lookup:', params);

            $.get(lookupUrl, params)
                .done(function (data) {
                    console.log('[MachineTracker] OK:', data);
                    renderTracker(data);
                    $('#tracker-result').removeClass('d-none');
                })
                .fail(function (xhr) {
                    console.error('[MachineTracker] Fail:', xhr.status, xhr.responseText);
                    let msg = 'Gagal memuat data mesin.';
                    if (xhr.status === 422 && xhr.responseJSON) {
                        const errors = xhr.responseJSON.errors || {};
                        msg = Object.values(errors).flat().join('\n');
                    } else if (xhr.status === 500) {
                        msg = 'Server error. Check logs.';
                    }
                    Swal.fire('Error', msg, 'error');
                })
                .always(function () {
                    $('#loading-spinner').addClass('d-none');
                });
        }

        $('#btn-search').on('click', doLookup);

        function renderTracker(data) {
            const m = data.machine;

            $('#machine-title').text(
                m.code.toUpperCase() +
                (m.name ? ' – ' + m.name : '') +
                ' (' + m.category + ')'
            );

            $('#machine-status-badge')
                .text(m.area)
                .attr('class', 'badge bg-blue-lt');

            if (data.active) {
                const a = data.active;
                $('#active-staff').text(a.staff_name);
                $('#active-date').text(a.date);
                $('#active-jam-pergi').text(a.jam_pergi);
                $('#active-catatan').text(a.catatan);
                if (a.photo_pergi) {
                    $('#active-photo').attr('src', a.photo_pergi).attr('title', 'Foto Pergi').removeClass('d-none');
                } else {
                    $('#active-photo').addClass('d-none');
                }
                $('#status-active').removeClass('d-none');
                $('#status-available').addClass('d-none');
            } else {
                $('#status-active').addClass('d-none');
                $('#status-available').removeClass('d-none');
            }

            const tbody = $('#history-tbody');
            tbody.empty();

            if (data.history.length === 0) {
                tbody.append('<tr><td colspan="8" class="text-center text-muted py-4">Belum ada riwayat.</td></tr>');
                return;
            }

            data.history.forEach(function (h) {
                const pergiHtml = h.photo_pergi
                    ? '<img src="' + h.photo_pergi + '" data-full-src="' + h.photo_pergi + '" class="lightbox-thumb" data-bs-toggle="modal" data-bs-target="#lightbox-modal" style="height:50px;width:50px;object-fit:cover;border-radius:4px;cursor:pointer;" title="Foto Mesin Pergi">'
                    : '<span class="text-muted">—</span>';

                const pulangHtml = h.photo_pulang
                    ? '<img src="' + h.photo_pulang + '" data-full-src="' + h.photo_pulang + '" class="lightbox-thumb" data-bs-toggle="modal" data-bs-target="#lightbox-modal" style="height:50px;width:50px;object-fit:cover;border-radius:4px;cursor:pointer;" title="Foto Mesin Pulang">'
                    : '<span class="text-muted">—</span>';

                tbody.append(
                    '<tr>' +
                        '<td>' + h.date + '</td>' +
                        '<td>' + h.staff_name + '</td>' +
                        '<td>' + h.jam_pergi + '</td>' +
                        '<td>' + h.jam_pulang + '</td>' +
                        '<td><span class="badge bg-muted-lt">' + h.durasi + '</span></td>' +
                        '<td class="text-muted small">' + h.catatan + '</td>' +
                        '<td>' + pergiHtml + '</td>' +
                        '<td>' + pulangHtml + '</td>' +
                    '</tr>'
                );
            });
        }

        let lightboxPhotos = [];
        let lightboxIndex = 0;

        // Collect photos from the row that triggered the modal
        $(document).on('show.bs.modal', '#lightbox-modal', function (e) {
            const trigger = $(e.relatedTarget);
            const src = trigger.data('full-src') || trigger.attr('src');
            $('#lightbox-img').attr('src', src);
            $('#lightbox-title').text(trigger.attr('title') || 'Foto');

            // Build sibling photos list for prev/next
            lightboxPhotos = [];
            lightboxIndex = 0;
            const $row = trigger.closest('tr');
            if ($row.length) {
                $row.find('.lightbox-thumb').each(function () {
                    lightboxPhotos.push({ src: $(this).data('full-src') || $(this).attr('src'), label: $(this).attr('title') });
                });
                lightboxIndex = $row.find('.lightbox-thumb').index(trigger);
            }

            if (lightboxPhotos.length > 1) {
                $('#lightbox-prev, #lightbox-next').removeClass('d-none');
            } else {
                $('#lightbox-prev, #lightbox-next').addClass('d-none');
            }
        });

        function showPrevPhoto() {
            lightboxIndex = (lightboxIndex - 1 + lightboxPhotos.length) % lightboxPhotos.length;
            $('#lightbox-img').attr('src', lightboxPhotos[lightboxIndex].src);
            $('#lightbox-title').text(lightboxPhotos[lightboxIndex].label);
        }

        function showNextPhoto() {
            lightboxIndex = (lightboxIndex + 1) % lightboxPhotos.length;
            $('#lightbox-img').attr('src', lightboxPhotos[lightboxIndex].src);
            $('#lightbox-title').text(lightboxPhotos[lightboxIndex].label);
        }

        $('#lightbox-prev').on('click', showPrevPhoto);
        $('#lightbox-next').on('click', showNextPhoto);

        $(document).on('click', '#active-photo', function () {
            $(this).attr('data-full-src', $(this).attr('src'));
        });

        $(document).on('keydown', function (e) {
            if (!$('#lightbox-modal').hasClass('show')) return;
            if (e.key === 'ArrowLeft') showPrevPhoto();
            if (e.key === 'ArrowRight') showNextPhoto();
        });
    }

    waitForJQuery();
});
</script>
@endpush
