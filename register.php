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
    <meta name="description" content="Create your SSRCMS account">
    <title>Register — ServiceDesk Pro</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#6c63ff">
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
                <p class="brand-tagline">Join the smart way to manage<br>service requests and complaints</p>
                <div class="brand-pills mt-5">
                    <span class="brand-pill"><i class="fas fa-check-circle"></i> Easy Submission</span>
                    <span class="brand-pill"><i class="fas fa-sync-alt"></i> Status Tracking</span>
                    <span class="brand-pill"><i class="fas fa-bell"></i> Notifications</span>
                </div>
            </div>
        </div>

        <!-- RIGHT: Register Form -->
        <div class="auth-form-panel">
            <div class="auth-card">

                <div class="d-lg-none text-center mb-3">
                    <div class="brand-logo-sm"><i class="fas fa-headset"></i></div>
                    <h5 class="fw-bold mt-2 text-white">ServiceDesk Pro</h5>
                </div>

                <h2 class="auth-title">Create Account ✨</h2>
                <p class="auth-subtitle">Fill in your details to get started</p>

                <form id="registerForm" novalidate autocomplete="off">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-medium" for="regName">Full Name</label>
                            <div class="custom-input-wrap">
                                <i class="fas fa-user input-icon-left"></i>
                                <input type="text" id="regName" name="name"
                                       class="form-control custom-inp" placeholder="John Doe" required>
                            </div>
                            <div class="field-error" id="nameErr"></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium" for="regEmail">Email Address</label>
                            <div class="custom-input-wrap">
                                <i class="fas fa-envelope input-icon-left"></i>
                                <input type="email" id="regEmail" name="email"
                                       class="form-control custom-inp" placeholder="you@example.com" required>
                            </div>
                            <div class="field-error" id="regEmailErr"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium" for="regPass">Password</label>
                            <div class="custom-input-wrap">
                                <i class="fas fa-lock input-icon-left"></i>
                                <input type="password" id="regPass" name="password"
                                       class="form-control custom-inp" placeholder="Min 6 characters" required>
                            </div>
                            <div class="field-error" id="regPassErr"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium" for="regConfirm">Confirm Password</label>
                            <div class="custom-input-wrap">
                                <i class="fas fa-lock input-icon-left"></i>
                                <input type="password" id="regConfirm" name="confirm"
                                       class="form-control custom-inp" placeholder="Repeat password" required>
                            </div>
                            <div class="field-error" id="confirmErr"></div>
                        </div>

                        <!-- Password strength bar -->
                        <div class="col-12">
                            <div class="strength-bar-wrap">
                                <div class="strength-bar" id="strengthBar"></div>
                            </div>
                            <small class="strength-label" id="strengthLabel"></small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium" for="regDept">Department</label>
                            <div class="custom-input-wrap">
                                <i class="fas fa-building input-icon-left"></i>
                                <input type="text" id="regDept" name="department"
                                       class="form-control custom-inp" placeholder="e.g. Engineering">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium" for="regPhone">Phone Number</label>
                            <div class="custom-input-wrap">
                                <i class="fas fa-phone input-icon-left"></i>
                                <input type="tel" id="regPhone" name="phone"
                                       class="form-control custom-inp" placeholder="10-digit number">
                            </div>
                        </div>
                    </div><!-- /.row -->

                    <button type="submit" class="btn btn-grad w-100 mt-4" id="registerBtn">
                        <span class="btn-label">Create Account</span>
                        <span class="btn-spin d-none"><i class="fas fa-spinner fa-spin"></i> Creating…</span>
                    </button>
                </form>

                <p class="text-center small mt-3" style="color:rgba(255,255,255,0.5);">
                    Already have an account? <a href="index.php" class="auth-link">Sign in</a>
                </p>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/app.js"></script>
    <script src="js/auth.js"></script>
    <script>
        $(document).ready(function () {
            initRegisterPage();
        });
    </script>
</body>
</html>
