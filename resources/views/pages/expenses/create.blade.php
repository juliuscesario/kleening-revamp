@extends('layouts.admin')

@section('title', 'Tambah Pengeluaran')

@section('content')
    <div class="container-xl">
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">Tambah Pengeluaran Baru</h2>
                </div>
            </div>
        </div>

        <div class="page-body">
            <div class="row row-cards">
                <div class="col-md-8 mx-auto">
                    <form action="{{ route('web.expenses.store') }}" method="POST" enctype="multipart/form-data"
                        class="card">
                        @csrf
                        <div class="card-header">
                            <h3 class="card-title">Form Pengeluaran</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label required">Tanggal</label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror" name="date"
                                    value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Nama Pengeluaran</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                                    list="expense-names" value="{{ old('name') }}" required
                                    placeholder="Contoh: Beli sabun cuci">
                                <datalist id="expense-names">
                                    @foreach($expenseNames as $name)
                                        <option value="{{ $name }}">
                                    @endforeach
                                </datalist>
                                <small class="form-hint">Ketik untuk melihat saran dari pengeluaran sebelumnya.</small>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Kategori</label>
                                        <select class="form-select @error('category_id') is-invalid @enderror"
                                            name="category_id" required>
                                            <option value="">Pilih Kategori</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label required">Nominal (Rp)</label>
                                        <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                            name="amount" value="{{ old('amount') }}" required min="0">
                                        @error('amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Bukti Foto</label>
                                <input type="file" class="form-control @error('photo') is-invalid @enderror" name="photo"
                                    accept="image/*">
                                <small class="form-hint">Format: JPG, PNG. Maksimal 10MB.</small>
                                @error('photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi / Keterangan Tambahan</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" name="description"
                                    rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <a href="{{ route('web.expenses.index') }}" class="btn btn-link link-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary ms-auto">Simpan Pengeluaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection