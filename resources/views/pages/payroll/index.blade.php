@extends('layouts.admin')
@section('title', 'Payroll')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Payroll</h2>
                <div class="text-muted mt-1">Download laporan payroll per staff per periode.</div>
            </div>
        </div>
    </div>

    <div class="page-body">
        {{-- Period Selector Card --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <select class="form-select" id="payroll-month">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m === $now->month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(2000, $m, 1)->locale('id')->isoFormat('MMMM') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select class="form-select" id="payroll-year">
                            @for($y = $now->year - 1; $y <= $now->year + 1; $y++)
                                <option value="{{ $y }}" {{ $y === $now->year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label d-block">Periode</label>
                        <div class="d-flex flex-wrap gap-3">
                            <label class="form-selectgroup">
                                <input type="radio" name="payroll-period" value="1" class="form-selectgroup-input form-check-input" {{ $autoPeriod === 1 ? 'checked' : '' }}>
                                <span class="form-selectgroup-label">Periode 1</span>
                            </label>
                            <label class="form-selectgroup">
                                <input type="radio" name="payroll-period" value="2" class="form-selectgroup-input form-check-input" {{ $autoPeriod === 2 ? 'checked' : '' }}>
                                <span class="form-selectgroup-label">Periode 2</span>
                            </label>
                            <label class="form-selectgroup">
                                <input type="radio" name="payroll-period" value="3" class="form-selectgroup-input form-check-input" {{ $autoPeriod === 3 ? 'checked' : '' }}>
                                <span class="form-selectgroup-label">Periode 3</span>
                            </label>
                        </div>
                        <div class="text-muted mt-2" id="period-info"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Staff List Card --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Staff Aktif</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama Staff</th>
                                <th>Area</th>
                                <th>Base Harian</th>
                                <th class="w-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="staff-list-body">
                            @forelse($staff as $index => $s)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $s->name }}</td>
                                    <td>{{ $s->area?->name ?? '-' }}</td>
                                    <td>Rp {{ number_format($s->base_harian ?? 80, 0, ',', '.') }},00</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-primary download-btn"
                                           data-staff-id="{{ $s->id }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><polyline points="7 11 12 16 17 11" /><line x1="12" y1="4" x2="12" y2="16" /></svg>
                                            Download
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada staff aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const monthSelect = document.getElementById('payroll-month');
    const yearSelect = document.getElementById('payroll-year');
    const periodRadios = document.querySelectorAll('input[name="payroll-period"]');
    const periodInfo = document.getElementById('period-info');
    const downloadButtons = document.querySelectorAll('.download-btn');

    const periodDescriptions = {
        1: 'Tgl kerja: 1-10 | Submit: tgl 11 | Bayar: tgl 12',
        2: 'Tgl kerja: 11-20 | Submit: tgl 21 | Bayar: tgl 22',
        3: 'Tgl kerja: 21-akhir bulan | Submit: tgl 1 bulan depan | Bayar: tgl 2 bulan depan',
    };

    function updatePeriodInfo() {
        const period = document.querySelector('input[name="payroll-period"]:checked')?.value;
        if (period && periodDescriptions[period]) {
            periodInfo.textContent = periodDescriptions[period];
        }
    }

    function updateDownloadLinks() {
        const month = monthSelect.value;
        const year = yearSelect.value;
        const period = document.querySelector('input[name="payroll-period"]:checked')?.value;

        if (!month || !year || !period) return;

        downloadButtons.forEach(btn => {
            const staffId = btn.dataset.staffId;
            btn.href = `/payroll/download/${staffId}/${year}/${month}/${period}`;
        });
    }

    // Event listeners
    monthSelect.addEventListener('change', updateDownloadLinks);
    yearSelect.addEventListener('change', updateDownloadLinks);
    periodRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            updatePeriodInfo();
            updateDownloadLinks();
        });
    });

    // Initialize
    updatePeriodInfo();
    updateDownloadLinks();
});
</script>
@endpush
