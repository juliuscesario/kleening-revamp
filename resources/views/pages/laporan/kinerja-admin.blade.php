@extends('layouts.admin')

@section('title', 'Laporan Kinerja Admin')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="page-pretitle">Laporan</div>
        <h2 class="page-title">Laporan Kinerja Admin</h2>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">

        {{-- Filter Card --}}
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('web.laporan.kinerja-admin') }}" class="row g-2 align-items-end">
                    <div class="col-auto">
                        <label class="form-label">Mulai</label>
                        <input type="date" name="mulai" class="form-control" value="{{ $mulai }}">
                    </div>
                    <div class="col-auto">
                        <label class="form-label">Sampai</label>
                        <input type="date" name="sampai" class="form-control" value="{{ $sampai }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-filter" width="24" height="24"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M4 4h16v2.172a2 2 0 0 1 -.586 1.414l-4.414 4.414v7l-4 -4v-3l-4.414 -4.414a2 2 0 0 1 -.586 -1.414v-2.172z"></path>
                            </svg>
                            Filter
                        </button>
                    </div>
                </form>
                {{-- Quick date buttons --}}
                <div class="mt-2 d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('today')">Today</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('yesterday')">Yesterday</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('this_month')">This Month</button>
                </div>
            </div>
        </div>

        {{-- Results Table --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Kinerja Admin — {{ \Carbon\Carbon::parse($mulai)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th class="text-center">Total SO Created</th>
                            <th class="text-center">Total SO Cancel</th>
                            <th class="text-center">Total SO Done</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adminStats as $stat)
                        <tr>
                            <td>{{ $stat['name'] }}</td>
                            <td class="text-center">
                                <span class="badge bg-blue-lt">{{ $stat['total_so'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-red-lt">{{ $stat['total_cancel'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-green-lt">{{ $stat['total_done'] }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Tidak ada data admin.</td>
                        </tr>
                        @endforelse
                    </tbody>

                    {{-- Totals row --}}
                    @if($adminStats->count() > 0)
                    <tfoot>
                        <tr class="fw-bold">
                            <td>TOTAL</td>
                            <td class="text-center">{{ $adminStats->sum('total_so') }}</td>
                            <td class="text-center">{{ $adminStats->sum('total_cancel') }}</td>
                            <td class="text-center">{{ $adminStats->sum('total_done') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function setDateRange(preset) {
    const mulai = document.querySelector('input[name="mulai"]');
    const sampai = document.querySelector('input[name="sampai"]');
    const today = new Date();
    // Use local date components to avoid UTC timezone shift
    const fmt = d => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${dd}`;
    };

    switch (preset) {
        case 'today':
            mulai.value = fmt(today);
            sampai.value = fmt(today);
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            mulai.value = fmt(yesterday);
            sampai.value = fmt(yesterday);
            break;
        case 'this_month':
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            mulai.value = fmt(firstDay);
            sampai.value = fmt(today);
            break;
    }

    // Auto-submit the form
    mulai.closest('form').submit();
}
</script>
@endpush
