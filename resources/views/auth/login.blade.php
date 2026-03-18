<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-base-path" content="{{ request()->getBasePath() }}">
    <title>Login - Trijaya</title>
    <link rel="manifest" href="{{ request()->getBasePath() }}/manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #fcbf3d;
            --accent-color: #ffae00ff;
            --text-color: #6d6f7bff;
        }

        body {
            font-family: 'Nunito', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            background-color: rgba(255, 255, 255, 1);
        }

        .login-container {
            max-width: 450px;
            width: 100%;
            animation: fadeIn 0.8s ease;
        }

        .login-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: none;
        }

        .card-header {
            background: var(--primary-color);
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        }

        .card-header h4 {
            font-weight: 700;
            color: black;
            position: relative;
        }

        .card-body {
            padding: 2.5rem;
            background-color: white;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }

        .input-group-text {
            background-color: transparent;
            border-left: none;
            cursor: pointer;
            color: var(--text-color);
        }

        .input-group .form-control {
            border-right: none;
        }

        .input-group .form-control:focus + .input-group-text {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-login {
            background-color: var(--primary-color);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            color: #000;
        }

        .btn-login:hover {
            background-color: var(--accent-color);
            color: #000;
            transform: translateY(-2px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .password-toggle {
            cursor: pointer;
            transition: all 0.3s;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .brand-logo {
            width: 120px;
            margin-bottom: 1.5rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            color: black;
            font-size: 0.9rem;
        }

        .footer-text a {
            color: black;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6 login-container">
                <div class="card login-card">
                    <div class="card-header">
                        <img src="{{ asset('file/TRUE_HOME_LOGO.jpg') }}"
                        alt="Trijaya LOGO" class="brand-logo rounded-circle">
                        <h4>Welcome to Trijaya</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('login.process') }}">
                            @csrf

                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    Invalid credentials. Please try again.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Enter your email">
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
                                    <span class="input-group-text password-toggle" id="togglePassword">
                                        <i class="bi bi-eye-fill" id="toggleIcon"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn-login">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                                </button>
                            </div>


                        </form>
                    </div>
                </div>
               <!--  <div class="footer-text">
                    © {{ date('Y') }} New Bendesa Cafe. All rights reserved.
                </div> -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle password visibility
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            const toggleIcon = document.querySelector('#toggleIcon');

            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);

                // Toggle eye icon
                if (type === 'password') {
                    toggleIcon.classList.remove('bi-eye-slash-fill');
                    toggleIcon.classList.add('bi-eye-fill');
                } else {
                    toggleIcon.classList.remove('bi-eye-fill');
                    toggleIcon.classList.add('bi-eye-slash-fill');
                }
            });

            // Add animation to form elements
            const formElements = document.querySelectorAll('.form-control, .btn-login');
            formElements.forEach((el, index) => {
                el.style.animationDelay = `${index * 0.1}s`;
                el.classList.add('animate__animated', 'animate__fadeInUp');
            });
        });
    </script>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                var basePathMeta = document.querySelector('meta[name="app-base-path"]')?.content || '';
                if (basePathMeta.endsWith('/')) basePathMeta = basePathMeta.slice(0, -1);
                var swPath = basePathMeta + '/serviceworker.js';
                var scope = (basePathMeta ? (basePathMeta + '/') : '/');

                navigator.serviceWorker.register(swPath, { scope: scope }).then(function(registration) {
                    console.log('✅ [Login] Service Worker registered:', registration.scope, 'from', swPath);
                }).catch(function(error) {
                    console.error('❌ [Login] Service Worker registration failed:', error);
                });
            });
        }
    </script>
</body>
</html>
