@extends('layouts.admin')
@section('title', 'Detail Customer')

@section('content')
<div class="container-xl">
    <!-- Page-header -->
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Detail Customer: {{ $customer->name }}</h2>
                <div class="text-muted mt-1">ID: CUST-{{ $customer->id }}</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('web.customers.index') }}" class="btn">Kembali</a>
            </div>
        </div>
    </div>

    <div class="page-body">
        <!-- Widgets -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-primary text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><line x1="9" y1="9" x2="10" y2="9" /><line x1="9" y1="13" x2="15" y2="13" /><line x1="9" y1="17" x2="15" y2="17" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Total Order</div>
                                <div class="text-muted">{{ $totalOrders }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-green text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Total Billing</div>
                                <div class="text-muted">Rp {{ number_format($totalBilling, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-danger text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0l8 -11" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Outstanding</div>
                                <div class="text-muted">Rp {{ number_format($outstanding, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="bg-warning text-white avatar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><rect x="4" y="5" width="16" height="16" rx="2" /><line x1="16" y1="3" x2="16" y2="7" /><line x1="8" y1="3" x2="8" y2="7" /><line x1="4" y1="11" x2="20" y2="11" /><rect x="8" y="15" width="2" height="2" /></svg>
                                </span>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Last Order</div>
                                <div class="text-muted">{{ $lastOrderDate ? \Carbon\Carbon::parse($lastOrderDate)->format('d M Y') : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address List -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">Daftar Alamat</h3>
            </div>
            <div class="list-group list-group-flush">
                @forelse($customer->addresses as $address)
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col">
                                <p class="mb-1"><strong>{{ $address->label }}:</strong> {{ $address->contact_name }} ({{ $address->contact_phone }})</p>
                                <p class="text-muted mb-0">{{ $address->full_address }}</p>
                            </div>
                            <div class="col-auto align-self-center">
                                @if($address->google_maps_link)
                                    <a href="{{ $address->google_maps_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-ghost-secondary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-map-pin" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 11a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /><path d="M17.657 16.657l-4.243 4.243a2 2 0 0 1 -2.827 0l-4.244 -4.243a8 8 0 1 1 11.314 0z" /></svg>
                                    </a>
                                @endif
                            </div>
                            <div class="col-auto">
                                <a href="#" class="btn btn-sm btn-primary">Buat SO</a>
                                <a href="#" class="btn btn-sm btn-warning edit-address" data-id="{{ $address->id }}">Edit</a>
                                <a href="#" class="btn btn-sm btn-danger delete-address" data-id="{{ $address->id }}">Hapus</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item">Customer ini belum memiliki alamat.</div>
                @endforelse
            </div>
        </div>

        <!-- Transaction List (Placeholder) -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Riwayat Transaksi</h3>
            </div>
            <div class="card-body">
                <p class="text-center text-muted">Fitur riwayat transaksi akan segera hadir.</p>
            </div>
        </div>
    </div>
</div>

{{-- Modal for editing address --}}
<div class="modal modal-blur fade" id="modal-edit-address" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="edit-address-form">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Alamat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit-address-id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Label Alamat</label>
                                <input type="text" class="form-control" name="label" id="edit-address-label">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Kontak</label>
                                <input type="text" class="form-control" name="contact_name" id="edit-address-contact_name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">No. Telepon Kontak</label>
                                <input type="text" class="form-control" name="contact_phone" id="edit-address-contact_phone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control" name="full_address" id="edit-address-full_address" rows="5"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Link Google Maps (Opsional)</label>
                                <input type="url" class="form-control" name="google_maps_link" id="edit-address-google_maps_link">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    const addressesApiUrl = '{{ url("api/addresses") }}';
    const editModalElement = document.getElementById('modal-edit-address');
    const editModalInstance = new bootstrap.Modal(editModalElement);

    // Handle Edit Address button click
    $('.edit-address').on('click', function(e) {
        e.preventDefault();
        const addressId = $(this).data('id');
        
        $.get(`${addressesApiUrl}/${addressId}`, function(response) {
            const address = response.data;
            $('#edit-address-id').val(address.id);
            $('#edit-address-label').val(address.label);
            $('#edit-address-contact_name').val(address.contact_name);
            $('#edit-address-contact_phone').val(address.contact_phone);
            $('#edit-address-full_address').val(address.full_address);
            $('#edit-address-google_maps_link').val(address.google_maps_link);
            editModalInstance.show();
        });
    });

    // Handle Edit Address form submission
    $('#edit-address-form').on('submit', function(e) {
        e.preventDefault();
        const addressId = $('#edit-address-id').val();
        const formData = $(this).serialize();

        $.ajax({
            url: `${addressesApiUrl}/${addressId}`,
            type: 'PUT',
            data: formData,
            success: function() {
                editModalInstance.hide();
                Swal.fire('Berhasil!', 'Alamat berhasil diperbarui.', 'success').then(() => {
                    location.reload(); // Reload page to see changes
                });
            },
            error: function(jqXHR) {
                if (jqXHR.status === 403) {
                    editModalInstance.hide();
                    Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak diizinkan mengedit alamat ini.', 'error');
                } else {
                    // Handle other errors like validation
                    alert('Terjadi kesalahan.');
                }
            }
        });
    });

    // Handle Delete Address button click
    $('.delete-address').on('click', function(e) {
        e.preventDefault();
        const addressId = $(this).data('id');

        Swal.fire({
            title: 'Anda yakin?',
            text: "Alamat ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${addressesApiUrl}/${addressId}`,
                    type: 'DELETE',
                    success: function() {
                        Swal.fire('Dihapus!', 'Alamat telah dihapus.', 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(jqXHR) {
                        if (jqXHR.status === 403) {
                            Swal.fire('Akses Ditolak!', jqXHR.responseJSON.message || 'Anda tidak diizinkan menghapus alamat ini.', 'error');
                        } else {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus alamat.', 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush
