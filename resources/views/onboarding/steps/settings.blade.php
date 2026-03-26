@extends('onboarding.layout')

@section('content')
<div class="card onboarding-card">
    <div class="card-header bg-white">
        <h3 class="card-title">Step 6: App Branding & Settings</h3>
    </div>
    <div class="card-body">
        <p class="text-muted">Personalize your application with your company logo and invoice details.</p>

        <form action="{{ route('onboarding.store', 'settings') }}" method="POST" enctype="multipart/form-data" class="onboarding-form">
            @csrf
            <div class="row g-4 justify-content-center">
                <div class="col-md-5 text-center">
                    <div class="mb-3">
                        <label class="form-label d-block text-start">Company Logo</label>
                        <div class="mb-2">
                             <span class="avatar avatar-xl avatar-rounded border bg-white" id="logo-preview">
                                 LOGO
                             </span>
                        </div>
                        <input type="file" name="logo" class="form-control" accept="image/*" onchange="previewImage(this)">
                        <div class="small text-muted mt-1">Recommended size: 512x512px. Max: 2MB.</div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="mb-3">
                        <label class="form-label">Company Address (for Invoice)</label>
                        <textarea name="company_address" class="form-control" rows="3" placeholder="Jl. Raya No. 123..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Invoice Footer Message</label>
                        <textarea name="invoice_text" class="form-control" rows="2" placeholder="e.g. Thank you for choosing our service! Our warranty period is 30 days."></textarea>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 border-top pt-3">
                <button type="submit" class="btn btn-primary btn-lg float-end">Save Branding & Last Step</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logo-preview');
                preview.innerHTML = '';
                preview.style.backgroundImage = 'url(' + e.target.result + ')';
                preview.style.backgroundSize = 'contain';
                preview.style.backgroundRepeat = 'no-repeat';
                preview.style.backgroundPosition = 'center';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
@endsection
