<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin.php' : 'dashboard.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to SSRCMS — Smart Service Request & Complaint Management System">
    <title>Login — ServiceDesk Pro</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#6c63ff">
    <link rel="apple-touch-icon" href="assets/icons/icon-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="auth-body">

    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999;"></div>

    <div class="auth-wrapper">

        <!-- LEFT: Branding -->
        <div class="auth-brand d-none d-lg-flex">
            <div class="brand-content text-center">
                <div class="brand-logo mb-4">
                    <i class="fas fa-headset"></i>
                </div>
                <h1 class="brand-name">ServiceDesk<br><span>Pro</span></h1>
                <p class="brand-tagline">Smart Service Request &amp;<br>Complaint Management System</p>
                <div class="brand-pills mt-5">
                    <span class="brand-pill"><i class="fas fa-bolt"></i> Real-time Tracking</span>
                    <span class="brand-pill"><i class="fas fa-shield-alt"></i> Secure Auth</span>
                    <span class="brand-pill"><i class="fas fa-mobile-alt"></i> PWA Ready</span>
                    <span class="brand-pill"><i class="fas fa-chart-pie"></i> Analytics</span>
                </div>
            </div>
        </div>

        <!-- RIGHT: Login Form -->
        <div class="auth-form-panel">
            <div class="auth-card">

                <!-- Mobile brand header -->
                <div class="d-lg-none text-center mb-4">
                    <div class="brand-logo-sm"><i class="fas fa-headset"></i></div>
                    <h5 class="fw-bold mt-2 text-white">ServiceDesk Pro</h5>
                </div>

                <h2 class="auth-title">Welcome Back 👋</h2>
                <p class="auth-subtitle">Sign in to your account to continue</p>

                <form id="loginForm" novalidate autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label fw-medium" for="loginEmail">Email Address</label>
                        <div class="custom-input-wrap">
                            <i class="fas fa-envelope input-icon-left"></i>
                            <input type="email" id="loginEmail" name="email"
                                   class="form-control custom-inp" placeholder="you@example.com" required>
                        </div>
                        <div class="field-error" id="emailErr"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium" for="loginPass">Password</label>
                        <div class="custom-input-wrap">
                            <i class="fas fa-lock input-icon-left"></i>
                            <input type="password" id="loginPass" name="password"
                                   class="form-control custom-inp pe-5" placeholder="Enter your password" required>
                            <button type="button" class="toggle-eye" id="togglePass" tabindex="-1">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="field-error" id="passErr"></div>
                    </div>

                    <div class="d-flex align-items-center mb-4">
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                            <label class="form-check-label small" for="rememberMe">Remember me for 30 days</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-grad w-100 mb-3" id="loginBtn">
                        <span class="btn-label">Sign In</span>
                        <span class="btn-spin d-none"><i class="fas fa-spinner fa-spin"></i> Signing in…</span>
                    </button>
                </form>

                <p class="text-center small mt-3" style="color:rgba(255,255,255,0.5);">
                    Don't have an account? <a href="register.php" class="auth-link">Register here</a>
                </p>

                <!-- Quick-fill demo cards -->
                <div class="demo-box mt-4">
                    <p class="demo-label"><i class="fas fa-flask me-1"></i> Quick Demo Access</p>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="demo-card demo-admin" id="fillAdmin"
                                 onclick="fillLogin('admin@servicedesk.com','Admin@123')" role="button">
                                <span class="demo-role">Admin</span>
                                <span class="demo-email-label">admin@servicedesk.com</span>
                                <span class="demo-pw-label">Admin@123</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="demo-card demo-user" id="fillUser"
                                 onclick="fillLogin('user@demo.com','User@123')" role="button">
                                <span class="demo-role">User</span>
                                <span class="demo-email-label">user@demo.com</span>
                                <span class="demo-pw-label">User@123</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div><!-- /.auth-wrapper -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script src="js/auth.js"></script>
    <script>
        function fillLogin(email, pass) {
            $('#loginEmail').val(email);
            $('#loginPass').val(pass);
            $('#loginEmail, #loginPass').removeClass('is-invalid').addClass('is-valid');
        }
        $(document).ready(function () {
            // Toggle password visibility
            $('#togglePass').on('click', function () {
                const inp = $('#loginPass');
                const isText = inp.attr('type') === 'text';
                inp.attr('type', isText ? 'password' : 'text');
                $(this).find('i').toggleClass('fa-eye fa-eye-slash');
            });
            initLoginPage();
            registerSW();
        });
    </script>
</body>
</html>
