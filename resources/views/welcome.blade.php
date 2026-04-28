<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Kleening.id') }} - Professional Cleaning Services</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
          (function() {
            const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-bs-theme', theme);
          })();
        </script>
    </head>
    <body class="antialiased">
        <div class="page">
            <header class="navbar navbar-expand-md d-print-none">
                <div class="container-xl">
                    <h1 class="navbar-brand navbar-brand-autodark d-none-block-768">
                        <a href="/">
                            @if(\App\Models\AppSetting::get('app_logo'))
                                <img src="{{ asset('storage/' . \App\Models\AppSetting::get('app_logo')) }}" alt="Logo" class="navbar-brand-image">
                            @else
                                <span class="h2 mb-0" style="letter-spacing: -1.5px; color: var(--off-black);">KLEENING</span>
                            @endif
                        </a>
                    </h1>
                    <div class="navbar-nav flex-row order-md-last ms-auto align-items-center">
                        <div class="nav-item d-none d-md-flex me-3">
                            <a href="#" class="nav-link px-0 hide-theme-dark" title="Enable dark mode" data-bs-toggle="tooltip" data-bs-placement="bottom" onclick="setTheme('dark')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z" /></svg>
                            </a>
                            <a href="#" class="nav-link px-0 hide-theme-light" title="Enable light mode" data-bs-toggle="tooltip" data-bs-placement="bottom" onclick="setTheme('light')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7" /></svg>
                            </a>
                        </div>
                        @if (Route::has('login'))
                            <div class="nav-item">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="btn btn-dark">Dashboard</a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Log in</a>
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="btn btn-primary">Register</a>
                                    @endif
                                @endauth
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <div class="page-wrapper">
                <main>
                    <div class="container-xl">
                        <div class="row align-items-center py-5">
                            <div class="col-lg-6">
                                <div class="mb-4">
                                    <span class="badge bg-primary-lt">OPERATIONAL EXCELLENCE</span>
                                </div>
                                <h1 class="display-3 mb-4">The Sophisticated Clean for Your Office.</h1>
                                <p class="lead text-muted mb-5" style="font-size: 1.25rem;">
                                    Premium, high-tech operational platform for modern cleaning management. 
                                    Experience structured geometry and editorial efficiency.
                                </p>
                                <div class="d-flex gap-3">
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Get Started</a>
                                    <a href="#" class="btn btn-outline-dark btn-lg">Learn More</a>
                                </div>
                            </div>
                            <div class="col-lg-6 d-none d-lg-block">
                                <div class="card p-0 overflow-hidden" style="border-radius: 12px; border: 1px solid var(--oat);">
                                    <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=1200" alt="Office" class="img-fluid">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-5">
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="mb-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-geometry" width="44" height="44" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                              <path d="M7 21l.5 -4.5c.667 -6 2.333 -9.5 5 -10.5c2.667 1 4.333 4.5 5 10.5l.5 4.5" />
                                              <path d="M7 21h10" />
                                              <path d="M12 3m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                            </svg>
                                        </div>
                                        <h3 class="h2 mb-3">Structured Geometry</h3>
                                        <p class="text-muted">Built with precision and industrial logic to ensure every task is tracked and managed perfectly.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="mb-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-dashboard" width="44" height="44" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                              <path d="M4 4h6v8h-6z" />
                                              <path d="M4 16h6v4h-6z" />
                                              <path d="M14 12h6v8h-6z" />
                                              <path d="M14 4h6v5h-6z" />
                                            </svg>
                                        </div>
                                        <h3 class="h2 mb-3">Editorial Layout</h3>
                                        <p class="text-muted">A dashboard that feels like a premium magazine, focusing on what matters most for your business.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="mb-3 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-bolt" width="44" height="44" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                              <path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0l8 -11" />
                                            </svg>
                                        </div>
                                        <h3 class="h2 mb-3">Operational Efficiency</h3>
                                        <p class="text-muted">Real-time tracking and automated scheduling to maximize your cleaning operations.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                
                <footer class="footer footer-transparent d-print-none mt-5">
                    <div class="container-xl">
                        <div class="row text-center align-items-center flex-row-reverse">
                            <div class="col-lg-auto ms-lg-auto">
                                <ul class="list-inline list-inline-dots mb-0">
                                    <li class="list-inline-item"><a href="#" class="link-secondary">Documentation</a></li>
                                    <li class="list-inline-item"><a href="#" class="link-secondary">Support</a></li>
                                </ul>
                            </div>
                            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                                <ul class="list-inline list-inline-dots mb-0">
                                    <li class="list-inline-item">
                                        Copyright &copy; {{ date('Y') }} Kleening.id. All rights reserved.
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </body>
    <script>
        function setTheme(theme) {
            document.documentElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);
            updateThemeVisibility(theme);
        }

        function updateThemeVisibility(theme) {
            const darkIcons = document.querySelectorAll('.hide-theme-dark');
            const lightIcons = document.querySelectorAll('.hide-theme-light');
            
            if (theme === 'dark') {
                darkIcons.forEach(el => el.style.display = 'none');
                lightIcons.forEach(el => el.style.display = 'block');
            } else {
                darkIcons.forEach(el => el.style.display = 'block');
                lightIcons.forEach(el => el.style.display = 'none');
            }
        }

        // Initialize visibility
        updateThemeVisibility(document.documentElement.getAttribute('data-bs-theme'));
    </script>
</html>
