<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login | Daily Report System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="CodedThemes">

    <link rel="icon" href="{{ asset('images/VasaHotel.png') }}" type="image/x-icon">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap"
        id="main-font-link">

    <link rel="stylesheet" href="{{ asset('template/dist') }}/assets/fonts/tabler-icons.min.css">

    <link rel="stylesheet" href="{{ asset('template/dist') }}/assets/css/style.css" id="main-style-link">
    <link rel="stylesheet" href="{{ asset('template/dist') }}/assets/css/style-preset.css">

    <style>
        /* Custom Style untuk Background */
        body {
            background-image: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=2070&auto=format&fit=crop');
            /* Gambar Restoran Elegan */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .auth-main {
            background: rgba(0, 0, 0, 0.5);
            /* Overlay gelap agar tulisan terbaca */
            min-height: 100vh;
        }

        .card {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            /* Bayangan kartu lebih elegan */
            border: none;
        }

        .logo-login {
            max-height: 80px;
            /* Membatasi tinggi logo agar proporsional */
            width: auto;
        }
    </style>
</head>

<body>
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    <div class="auth-main">
        <div class="auth-wrapper v3">
            <div class="auth-form">
                <div class="card my-5">
                    <div class="card-body p-5">

                        {{-- HEADER: LOGO & JUDUL --}}
                        <div class="text-center mb-4">
                            {{-- Ganti src ini dengan logo hotel Anda yang benar --}}
                            <img src="{{ asset('images/VasaHotel.png') }}" alt="Logo Hotel"
                                class="img-fluid logo-login mb-3">
                            <h3 class="f-w-600 mb-1">Daily Report</h3>
                            <p class="text-muted">Enter your credentials to continue</p>
                        </div>

                        {{-- ALERT ERROR (Jika Login Gagal) --}}
                        @if ($errors->any())
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="ti ti-alert-circle me-2 fs-4"></i>
                                <div>
                                    Login Failed. Please check your ID/Password.
                                </div>
                            </div>
                        @endif

                        {{-- FORM LOGIN --}}
                        <form method="POST" action="{{ route('login') }}" id="loginForm">
                            @csrf

                            {{-- 1. INPUT NIK --}}
                            <div class="form-group mb-3">
                                <label class="form-label">Employee ID (NIK)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-user"></i></span>
                                    <input type="text" name="nik" class="form-control"
                                        placeholder="Enter your NIK" value="{{ old('nik') }}" required autofocus>
                                </div>
                                @error('nik')
                                    <small class="text-danger mt-1">{{ $message }}</small>
                                @enderror
                            </div>

                            {{-- 2. INPUT PASSWORD --}}
                            <div class="form-group mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-lock"></i></span>
                                    <input type="password" name="password" class="form-control" id="passwordInput"
                                        placeholder="Enter Password" required autocomplete="off">
                                    {{-- Tombol Show/Hide Password --}}
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="ti ti-eye" id="eyeIcon"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- 3. REMEMBER ME --}}
                            <div class="d-flex mt-1 justify-content-between align-items-center">
                                <div class="form-check">
                                    <input class="form-check-input input-primary" type="checkbox" id="remember_me"
                                        name="remember">
                                    <label class="form-check-label text-muted" for="remember_me">Remember me</label>
                                </div>
                            </div>

                            {{-- 4. TOMBOL LOGIN --}}
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary" id="btnSubmit">
                                    <span id="btnText">Login</span>
                                    <span id="btnLoader" class="spinner-border spinner-border-sm d-none" role="status"
                                        aria-hidden="true"></span>
                                </button>
                            </div>
                        </form>

                        <hr>
                        <div class="text-center">
                            <p class="mb-0 text-muted small">Make sure your account is registered by Admin.</p>
                        </div>

                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="text-center text-white">
                    <p class="my-1">Copyright © {{ date('Y') }} Vasa Hotel Surabaya</p>
                </div>

            </div>
        </div>
    </div>

    <script src="{{ asset('template/dist') }}/assets/js/plugins/popper.min.js"></script>
    <script src="{{ asset('template/dist') }}/assets/js/plugins/simplebar.min.js"></script>
    <script src="{{ asset('template/dist') }}/assets/js/plugins/bootstrap.min.js"></script>
    <script src="{{ asset('template/dist') }}/assets/js/fonts/custom-font.js"></script>
    <script src="{{ asset('template/dist') }}/assets/js/pcoded.js"></script>
    <script src="{{ asset('template/dist') }}/assets/js/plugins/feather.min.js"></script>

    <script>
        layout_change('light');
    </script>

    {{-- SCRIPT TAMBAHAN: INTERAKSI LOGIN --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Show/Hide Password Logic
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#passwordInput');
            const eyeIcon = document.querySelector('#eyeIcon');

            togglePassword.addEventListener('click', function(e) {
                // Toggle tipe input
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);

                // Toggle icon
                if (type === 'text') {
                    eyeIcon.classList.remove('ti-eye');
                    eyeIcon.classList.add('ti-eye-off');
                } else {
                    eyeIcon.classList.remove('ti-eye-off');
                    eyeIcon.classList.add('ti-eye');
                }
            });

            // 2. Loading State saat Submit
            const loginForm = document.querySelector('#loginForm');
            const btnSubmit = document.querySelector('#btnSubmit');
            const btnText = document.querySelector('#btnText');
            const btnLoader = document.querySelector('#btnLoader');

            loginForm.addEventListener('submit', function() {
                // Matikan tombol agar tidak bisa diklik 2x
                btnSubmit.disabled = true;
                btnText.textContent = "Signing in...";
                btnLoader.classList.remove('d-none');
            });
        });
    </script>

</body>

</html>
