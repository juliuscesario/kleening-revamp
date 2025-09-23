@extends('layouts.admin')

@section('title', 'Manajemen Area')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Manajemen Area
                </h2>
                <div class="text-muted mt-1">Daftar semua area operasional.</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div class="btn-list">
                    <a href="#" id="add-area-button" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                        Tambah Area Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    {{-- INI ADALAH TABEL YANG AKAN DIISI DATA --}}
                    <table id="areas-table" class="table card-table table-vcenter text-nowrap datatable" width="100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Area</th>
                                <th>Dibuat Pada</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal (Pop-up Form) untuk Tambah & Edit Area --}}
<div class="modal modal-blur fade" id="modal-area" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="area-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-title">Tambah Area Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="area-id">
                    <div class="mb-3">
                        <label class="form-label">Nama Area</label>
                        <input type="text" class="form-control" name="name" id="area-name" placeholder="Contoh: Jakarta Selatan">
                        <div class="invalid-feedback" id="name-error"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="submit-button">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Menunggu sampai seluruh halaman dan skrip utama (app.js) selesai dimuat
    document.addEventListener('DOMContentLoaded', function () {
        // Mendapatkan token dari local storage (disimpan saat login)
        const authToken = localStorage.getItem('auth_token');

        // Jika tidak ada token, jangan lanjutkan
        if (!authToken) {
            console.error('Authentication Token not found.');
            // Mungkin arahkan ke halaman login
            window.location.href = '/login';
            return;
        }

        // Inisialisasi DataTables
        const table = $('#areas-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('data.areas') }}", // Menggunakan nama route API
                type: 'GET',
                beforeSend: function (xhr) {
                    // Menambahkan header Authorization di setiap request DataTables
                    xhr.setRequestHeader('Authorization', 'Bearer ' + authToken);
                    xhr.setRequestHeader('Accept', 'application/json');
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'created_at', name: 'created_at', render: function(data) {
                    // Format tanggal menjadi lebih mudah dibaca
                    return new Date(data).toLocaleDateString('id-ID', {
                        day: '2-digit', month: 'long', year: 'numeric'
                    });
                }},
                { data: 'id', name: 'action', orderable: false, searchable: false, render: function(data, type, row) {
                    // Tombol Edit dan Hapus
                    return `
                        <button class="btn btn-sm btn-warning edit-button" data-id="${data}" data-name="${row.name}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-button" data-id="${data}">Hapus</button>
                    `;
                }}
            ]
        });

        // --- AKSI TOMBOL TAMBAH ---
        $('#add-area-button').on('click', function() {
            $('#area-form')[0].reset();
            $('.is-invalid').removeClass('is-invalid'); // Hapus error validasi lama
            $('#modal-title').text('Tambah Area Baru');
            $('#area-id').val('');
            $('#modal-area').modal('show');
        });

        // --- AKSI TOMBOL EDIT ---
        $('#areas-table').on('click', '.edit-button', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            $('#area-form')[0].reset();
            $('.is-invalid').removeClass('is-invalid');
            $('#modal-title').text('Edit Area: ' + name);
            $('#area-id').val(id);
            $('#area-name').val(name);
            $('#modal-area').modal('show');
        });

        // --- AKSI SUBMIT FORM (Untuk Simpan & Update) ---
        $('#area-form').on('submit', function(e) {
            e.preventDefault();
            $('.is-invalid').removeClass('is-invalid'); // Hapus validasi error lama
            
            var id = $('#area-id').val();
            var url = id ? `{{ url('api/areas') }}/${id}` : "{{ route('areas.store') }}";
            var method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + authToken);
                },
                success: function(response) {
                    $('#modal-area').modal('hide');
                    table.ajax.reload(); // Muat ulang data di tabel
                    alert('Data berhasil disimpan!');
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // Error validasi
                        var errors = xhr.responseJSON.errors;
                        if (errors.name) {
                            $('#area-name').addClass('is-invalid');
                            $('#name-error').text(errors.name[0]);
                        }
                    } else {
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    }
                }
            });
        });

        // --- AKSI TOMBOL HAPUS ---
        $('#areas-table').on('click', '.delete-button', function() {
            var id = $(this).data('id');
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                $.ajax({
                    url: `{{ url('api/areas') }}/${id}`,
                    method: 'DELETE',
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Authorization', 'Bearer ' + authToken);
                    },
                    success: function() {
                        table.ajax.reload();
                        alert('Data berhasil dihapus!');
                    },
                    error: function() {
                        alert('Gagal menghapus data.');
                    }
                });
            }
        });

    });
</script>
@endpush