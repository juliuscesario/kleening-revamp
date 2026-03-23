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
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .onboarding-step-active { border-bottom: 3px solid var(--tblr-primary); }
        .onboarding-card { border-radius: 12px; border: none; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50/50">
    <div class="page">
        <header class="navbar navbar-expand-md d-print-none bg-white border-bottom">
            <div class="container-xl">
                <h1 class="navbar-brand">
                    <a href=".">
                        {{ config('app.name') }} <span class="badge bg-blue-lt ms-2">Onboarding</span>
                    </a>
                </h1>
                <div class="navbar-nav flex-row order-md-last ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                            <div class="d-none d-xl-block ps-2">
                                <div>{{ Auth::user()->name }}</div>
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

        <div class="page-wrapper pt-4">
            <div class="container-xl">
                <div class="row row-cards justify-content-center">
                    <div class="col-md-10 col-lg-8">
                        <!-- Progress Steps -->
                        <div class="card mb-4 onboarding-card">
                            <div class="card-body py-3">
                                <div class="steps steps-blue border-0 my-0">
                                    @foreach($allSteps as $s)
                                        <div class="step-item {{ $s->status === 'completed' ? 'active' : '' }} {{ $s->step === $step->step ? 'current' : '' }}">
                                            {{ $steps[$s->step] }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
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
                    
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

                    fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.next || data.redirect) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Step Completed!',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = data.next || data.redirect;
                            });
                        } else if (data.error) {
                            Swal.fire('Error', data.error, 'error');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Something went wrong', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
