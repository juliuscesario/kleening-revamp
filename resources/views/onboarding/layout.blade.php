<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Onboarding - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .onboarding-card { border-radius: 16px; border: none; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); overflow: hidden; }
        
        /* New Premium Stepper */
        .stepper { display: flex; justify-content: space-between; position: relative; margin-bottom: 2.5rem; padding: 0; }
        .stepper::before { content: ''; position: absolute; top: 20px; left: 0; right: 0; height: 3px; background: #f1f5f9; z-index: 1; border-radius: 10px; }
        .stepper-progress { position: absolute; top: 20px; left: 0; height: 3px; background: linear-gradient(90deg, #206bc4, #4299e1); z-index: 2; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 10px; }
        
        .stepper-item { position: relative; z-index: 3; display: flex; flex-direction: column; align-items: center; flex: 1; }
        .stepper-circle { width: 44px; height: 44px; border-radius: 50%; background: #fff; border: 3px solid #f1f5f9; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; color: #94a3b8; transition: all 0.4s ease; cursor: default; box-shadow: 0 0 0 4px #f8fafc; }
        .stepper-label { margin-top: 10px; font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: none; letter-spacing: -0.01em; text-align: center; transition: all 0.3s; }
        
        .stepper-item.completed .stepper-circle { background: #206bc4; border-color: #206bc4; color: #fff; transform: scale(0.9); box-shadow: 0 4px 10px rgba(32, 107, 196, 0.3); }
        .stepper-item.completed .stepper-label { color: #64748b; font-weight: 500; }
        .stepper-item.current .stepper-circle { background: #fff; border-color: #206bc4; color: #206bc4; transform: scale(1.1); box-shadow: 0 0 20px rgba(32, 107, 196, 0.15), 0 0 0 6px rgba(32, 107, 196, 0.1); }
        .stepper-item.current .stepper-label { color: #0f172a; transform: translateY(2px); }
        
        .btn-primary { background: #206bc4; border-color: #206bc4; border-radius: 8px; padding: 10px 24px; font-weight: 600; transition: all 0.2s; }
        .btn-primary:hover { background: #1a569d; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(32, 107, 196, 0.25); }
        .font-bold { font-weight: 700; }
        .bg-blue-lt { background-color: #dceefc !important; color: #206bc4 !important; }
    </style>
</head>
<body class="bg-gray-50/50">
    <div class="page">
        <header class="navbar navbar-expand-md d-print-none bg-white border-bottom">
            <div class="container-xl">
                <h1 class="navbar-brand">
                    <a href="." class="d-flex align-items-center">
                        <span class="text-primary fw-bold me-2">Servis</span>Bos
                        <span class="badge bg-blue-lt ms-3 px-3 py-2 rounded-pill" style="font-size: 0.7rem;">ONBOARDING</span>
                    </a>
                </h1>
                <div class="navbar-nav flex-row order-md-last ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm rounded-circle bg-blue-lt fw-bold">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                            <div class="d-none d-xl-block ps-2">
                                <div class="small fw-bold">{{ Auth::user()->name }}</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="page-wrapper pt-5">
            <div class="container-xl">
                <div class="row row-cards justify-content-center">
                    <div class="col-md-11 col-lg-10">
                        <!-- Progress Stepper -->
                        <div class="stepper">
                            @php 
                                $totalSteps = count($allSteps);
                                $currentIndex = 0;
                                foreach($allSteps as $idx => $s) {
                                    if ($s->step === $step->step) {
                                        $currentIndex = $idx;
                                        break;
                                    }
                                }
                                $progressWidth = ($totalSteps > 1) ? ($currentIndex / ($totalSteps - 1)) * 100 : 0;
                            @endphp
                            <div class="stepper-progress" style="width: {{ $progressWidth }}%;"></div>
                            @foreach($allSteps as $index => $s)
                                <div class="stepper-item {{ $s->status === 'completed' ? 'completed' : '' }} {{ $s->step === $step->step ? 'current' : '' }}">
                                    <div class="stepper-circle">
                                        @if($s->status === 'completed')
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check" width="24" height="24" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                <path d="M5 12l5 5l10 -10"></path>
                                            </svg>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </div>
                                    <div class="stepper-label d-none d-md-block">{{ $steps[$s->step] }}</div>
                                </div>
                            @endforeach
                        </div>

                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>
    @vite(['resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.onboarding-form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    const isFile = this.querySelector('input[type="file"]')?.files.length > 0;
                    
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = isFile ? '<span class="spinner-border spinner-border-sm me-2"></span> Uploading (0%)...' : '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', this.action);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');

                    if (isFile) {
                        xhr.upload.addEventListener('progress', (e) => {
                            if (e.lengthComputable) {
                                const percent = Math.round((e.loaded / e.total) * 100);
                                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Uploading (${percent}%)...`;
                                if (percent === 100) {
                                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing on Server...';
                                }
                            }
                        });
                    }

                    xhr.onload = function() {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            const data = JSON.parse(xhr.responseText);
                            if (data.next || data.redirect) {
                                Swal.fire({
                                    icon: 'success',
                                    title: data.message || 'Saved successfully!',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = data.next || data.redirect;
                                });
                            } else if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            }
                        } else {
                            let errorMsg = 'Something went wrong';
                            try {
                                const data = JSON.parse(xhr.responseText);
                                errorMsg = data.error || data.message || 'Something went wrong';
                                if (data.errors) {
                                    errorMsg = Object.values(data.errors).flat().join('<br>');
                                }
                            } catch (e) {
                                errorMsg = 'Server Error: ' + xhr.status + ' ' + xhr.statusText;
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: errorMsg
                            });
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    };

                    xhr.onerror = function() {
                        Swal.fire('Error', 'Network error or request failed', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    };

                    xhr.send(formData);
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
