@extends('layouts.admin')

@section('title', 'Laporan Absen Staff')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="page-pretitle">Laporan</div>
        <h2 class="page-title">Laporan Absen Staff</h2>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <div class="d-flex">
                <div class="me-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                </div>
                <div>{{ session('success') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        @endif

        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible" role="alert">
            <div class="d-flex">
                <div class="me-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>
                </div>
                <div>{{ session('warning') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert">
            <div class="d-flex">
                <div class="me-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>
                </div>
                <div>{{ session('error') }}</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert"></a>
        </div>
        @endif

        {{-- Filter Card --}}
        <div class="card mb-3">
            <div class="card-body">
                {{-- GET form for filter --}}
                <form method="GET" action="{{ route('web.laporan.absen-staff') }}" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label">Dari</label>
                        <input type="date" name="dari" class="form-control" value="{{ $dari }}">
                    </div>
                    <div class="col-auto">
                        <label class="form-label">Sampai</label>
                        <input type="date" name="sampai" class="form-control" value="{{ $sampai }}">
                    </div>
                    <div class="col-auto">
                        <label class="form-label">Staff</label>
                        <select name="nik" class="form-select">
                            <option value="">Semua Staff</option>
                            @foreach($staffList as $s)
                            <option value="{{ $s->hadirr_nik }}" {{ $filterNik === $s->hadirr_nik ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            @foreach($statusList as $s)
                            <option value="{{ $s->status }}" {{ $filterStatus === $s->status ? 'selected' : '' }}>{{ $s->status }} — {{ $s->raw_status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-filter" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-4 -4v-3l-4.414 -4.414a2 2 0 0 1 -.586 -1.414v-2.172z"></path>
                            </svg>
                            Tampilkan
                        </button>
                    </div>
                </form>

                {{-- POST form for sync --}}
                <form method="POST" action="{{ route('web.laporan.absen-staff.sync') }}" id="syncForm" class="mt-2">
                    @csrf
                    <input type="hidden" name="dari" value="{{ $dari }}">
                    <input type="hidden" name="sampai" value="{{ $sampai }}">
                    <button type="submit" class="btn btn-outline-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh" width="24" height="24"
                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                            <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
                            <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
                        </svg>
                        Sync dari Hadirr
                    </button>
                </form>
            </div>
        </div>

        {{-- Last sync info --}}
        <div class="mb-3">
            @if($lastSync)
            <small class="text-muted">Terakhir sync: {{ \Carbon\Carbon::parse($lastSync)->format('d M Y H:i') }}</small>
            @else
            <small class="text-muted">Belum pernah sync untuk periode ini.</small>
            @endif
        </div>

        {{-- Data table card — pivot/matrix view --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Absensi Staff — {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}</h3>
            </div>
            <div class="card-body p-0">
                @if($attendances->isEmpty())
                <div class="text-center text-muted py-5">Tidak ada data absensi untuk periode ini.</div>
                @else
                <div class="table-responsive" style="max-height: 80vh; overflow: auto;">
                    <table class="table table-bordered table-vcenter mb-0" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <th style="position: sticky; left: 0; z-index: 2; background: var(--tblr-bg-surface, #1a2234); min-width: 150px;">
                                    Nama Staff
                                </th>
                                @foreach($dates as $date)
                                    <th class="text-center" style="min-width: 90px;">
                                        {{ \Carbon\Carbon::parse($date)->format('d') }}<br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($date)->translatedFormat('D') }}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($staffNames as $nik => $nama)
                                <tr>
                                    <td style="position: sticky; left: 0; z-index: 1; background: var(--tblr-bg-surface, #1a2234); font-weight: 600;">
                                        {{ $loop->iteration }}. {{ $nama }}
                                    </td>
                                    @foreach($dates as $date)
                                        @php
                                            $dateKey = \Carbon\Carbon::parse($date)->format('Y-m-d');
                                            $att = $pivot[$nik][$dateKey] ?? null;
                                        @endphp
                                        <td class="text-center" style="padding: 4px 6px;"
                                            @if($att)
                                                title="Clock in: {{ $att->clock_in ? $att->clock_in->format('H:i') : '—' }} | Clock out: {{ $att->clock_out ? $att->clock_out->format('H:i') : '—' }} | Status: {{ $att->raw_status ?? $att->status }} | Durasi: {{ $att->clock_in && $att->clock_out ? $att->clock_in->diff($att->clock_out)->h . 'j ' . $att->clock_in->diff($att->clock_out)->i . 'm' : '—' }}"
                                            @endif>
                                            @if($att)
                                                @if($att->status === 'NW' || $att->status === 'JH')
                                                    <span class="text-muted">{{ $att->status }}</span>
                                                @elseif($att->status === 'A')
                                                    <span class="text-danger fw-bold">A</span>
                                                @elseif($att->status === 'S' || $att->status === 'L' || $att->status === 'O' || $att->status === 'UL' || $att->status === 'HL')
                                                    <span class="text-info">{{ $att->status }}</span>
                                                @else
                                                    <div style="line-height: 1.3;">
                                                        <span class="text-success">{{ $att->clock_in ? $att->clock_in->format('H:i') : '—' }}</span>
                                                        <br>
                                                        <span class="text-danger">{{ $att->clock_out ? $att->clock_out->format('H:i') : '—' }}</span>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                <div class="px-3 pt-2 pb-3 text-muted small">
                    <span class="text-success">■</span> Clock In &nbsp;
                    <span class="text-danger">■</span> Clock Out &nbsp;
                    <span class="badge bg-danger-lt">A</span> Absen &nbsp;
                    <span class="badge bg-info-lt">S/L/O</span> Sakit/Cuti/Izin &nbsp;
                    <span class="badge bg-secondary-lt">NW/JH</span> Libur
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#syncForm').on('submit', function() {
        var btn = $(this).find('button[type=submit]');
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Sedang sync...');
    });
});
</script>
@endpush
