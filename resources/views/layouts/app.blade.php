<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - DELTA</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('mazer/assets/static/images/favicon.svg') }}" type="image/x-icon">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('mazer/assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/extensions/sweetalert2/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/extensions/choices.js/public/assets/styles/choices.css') }}">

    <link rel="stylesheet" href="{{ asset('mazer/assets/extensions/flatpickr/flatpickr.min.css') }}">

    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/app-dark.css') }}">
    <link rel="stylesheet" href="{{ asset('mazer/assets/compiled/css/iconly.css') }}">

    @stack('styles')
</head>

<body>
    <script src="{{ asset('mazer/assets/static/js/initTheme.js') }}"></script>

    <div id="app">
        <!-- Sidebar -->
        @include('components.sidebar')

        <div id="main">
            <header class="mb-3 d-flex justify-content-end align-items-center">

                <!-- Burger -->
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>

                <!-- Right Menu -->
                <div class="dropdown">
                    <a href="#" data-bs-toggle="dropdown" class="d-flex align-items-center">
                        <div class="avatar avatar-md">
                            <img src="{{ auth()->user()->avatar ?? asset('mazer/assets/compiled/jpg/1.jpg') }}">
                        </div>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                            <small class="text-muted">{{ auth()->user()->email }}</small>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="bi bi-person"></i> Profile
                            </a>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="w-100">
                                @csrf
                                <button type="submit" class="btn btn-link text-danger">
                                    <i class="bi bi-box-arrow-right me-3 text-danger"></i>
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>

            </header>

            <div class="page-heading">
                <h3>@yield('page-title')</h3>
            </div>

            <div class="page-content">
                @yield('content')
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        {{-- <p>2025 &copy; Mazer Laravel</p> --}}
                    </div>
                    {{-- <div class="float-end">
                        <p>Crafted with <span class="text-danger"><i class="bi bi-heart-fill icon-mid"></i></span>
                            by <a href="https://saugi.me">Saugi</a></p>
                    </div> --}}
                </div>
            </footer>
        </div>
    </div>

    <!-- JS -->
    <script src="{{ asset('mazer/assets/static/js/components/dark.js') }}"></script>
    <script src="{{ asset('mazer/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('mazer/assets/compiled/js/app.js') }}"></script>

    <script src="{{ asset('mazer/assets/extensions/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('mazer/assets/extensions/parsleyjs/parsley.min.js') }}"></script>
    <script src="{{ asset('mazer/assets/static/js/pages/parsley.js') }}"></script>
    <script src="{{ asset('mazer/assets/extensions/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('mazer/assets/extensions/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    {{-- <script src="{{ asset('mazer/assets/static/js/pages/datatables.js') }}"></script> --}}
    <script src="{{ asset('mazer/assets/extensions/sweetalert2/sweetalert2.min.js') }}"></script>>
    <script src="{{ asset('mazer/assets/extensions/sweetalert2/sweetalert2.min.js') }}"></script>>
    <script src="{{ asset('mazer/assets/extensions/choices.js/public/assets/scripts/choices.js') }}"></script>
    <script src="{{ asset('mazer/assets/static/js/pages/form-element-select.js') }}"></script>

    <script src="{{ asset('mazer/assets/extensions/flatpickr/flatpickr.min.js') }}"></script>
    <script src="{{ asset('mazer/assets/static/js/pages/date-picker.js') }}"></script>

    <!-- ApexCharts (hanya jika dibutuhkan) -->
    <script>
        $(function() {
            // Setup CSRF for AJAX
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            window.toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            // Wrapper toast
            window.toastSuccess = (message) => {
                toast.fire({ icon: 'success', title: message });
            };

            window.toastError = (message) => {
                toast.fire({ icon: 'error', title: message });
            };

            window.toastWarning = (message) => {
                toast.fire({ icon: 'warning', title: message });
            };

            window.toastInfo = (message) => {
                toast.fire({ icon: 'info', title: message });
            };
        });
    </script>
    @stack('scripts')
</body>

</html>
