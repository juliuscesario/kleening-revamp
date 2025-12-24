@extends('layouts.admin')

@section('title', 'Application Settings')

@section('content')
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">General Settings</h3>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('web.settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('POST')

                            <div class="mb-3">
                                <label class="form-label required">Application Name</label>
                                <input type="text" class="form-control @error('app_name') is-invalid @enderror"
                                    name="app_name" value="{{ old('app_name', $appName) }}" placeholder="My Awesome App">
                                @error('app_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">This name will appear on the browser tab and login screen.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Application Logo</label>
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="avatar avatar-xl"
                                            style="background-image: url('{{ $appLogo ? asset('storage/' . $appLogo) : asset('storage/logo_kleening.png') }}')"></span>
                                    </div>
                                    <div class="col">
                                        <input type="file" class="form-control @error('app_logo') is-invalid @enderror"
                                            name="app_logo" accept="image/*">
                                        @error('app_logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-hint">Recommended size: 200x200px. Upload to replace the current
                                            logo.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Invoice Payment Info & Footer</label>
                                <textarea class="form-control @error('invoice_footer_text') is-invalid @enderror"
                                    name="invoice_footer_text"
                                    rows="10">{{ old('invoice_footer_text', $invoiceFooterText) }}</textarea>
                                @error('invoice_footer_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-hint">You can use HTML tags (e.g., &lt;strong&gt;, &lt;p&gt;, &lt;br&gt;)
                                    for formatting.</small>
                            </div>

                            <div class="card-footer text-end">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection