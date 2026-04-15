/* ─── auth.js : Login & Register jQuery AJAX + Validation ─────── */

// ─── Password Strength ─────────────────────────────────────────
function checkStrength(password) {
    let score = 0;
    if (password.length >= 6)                     score++;
    if (password.length >= 10)                    score++;
    if (/[A-Z]/.test(password))                  score++;
    if (/[0-9]/.test(password))                  score++;
    if (/[^A-Za-z0-9]/.test(password))           score++;

    const levels = [
        { pct:  0, color: 'transparent',     label: '' },
        { pct: 20, color: 'var(--danger)',    label: 'Very Weak' },
        { pct: 40, color: 'var(--accent)',    label: 'Weak' },
        { pct: 60, color: 'var(--warning)',   label: 'Fair' },
        { pct: 80, color: 'var(--info)',      label: 'Good' },
        { pct:100, color: 'var(--success)',   label: 'Strong 💪' },
    ];
    return levels[score] || levels[0];
}

// ─── Validate Email ────────────────────────────────────────────
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ─── Show / Clear Field Error ──────────────────────────────────
function setFieldError(inputId, errId, msg) {
    const inp = $('#' + inputId);
    const err = $('#' + errId);
    if (msg) {
        inp.addClass('is-invalid').removeClass('is-valid');
        err.text(msg);
    } else {
        inp.removeClass('is-invalid').addClass('is-valid');
        err.text('');
    }
    return !msg;
}

// ─── LOGIN PAGE ────────────────────────────────────────────────
function initLoginPage() {

    // Real-time validation
    $('#loginEmail').on('blur', function() {
        const v = $(this).val().trim();
        setFieldError('loginEmail', 'emailErr', !v ? 'Email is required.' : !isValidEmail(v) ? 'Enter a valid email.' : '');
    });
    $('#loginPass').on('blur', function() {
        setFieldError('loginPass', 'passErr', !$(this).val() ? 'Password is required.' : '');
    });

    // Form submit
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();

        const email    = $('#loginEmail').val().trim();
        const password = $('#loginPass').val();
        const remember = $('#rememberMe').is(':checked') ? 'true' : 'false';

        let ok = true;
        ok = setFieldError('loginEmail', 'emailErr',
            !email ? 'Email is required.' : !isValidEmail(email) ? 'Enter a valid email.' : '') && ok;
        ok = setFieldError('loginPass', 'passErr',
            !password ? 'Password is required.' : '') && ok;
        if (!ok) return;

        // Start loader
        $('#loginBtn .btn-label').addClass('d-none');
        $('#loginBtn .btn-spin').removeClass('d-none');
        $('#loginBtn').prop('disabled', true);

        $.ajax({
            url:  '/SSRCMS/api/login.php',
            type: 'POST',
            data: { email, password, remember },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast('Welcome back, ' + res.name + '! Redirecting…', 'success');
                    setTimeout(function () {
                        // Use relative redirect to preserve port number
                        if (res.role === 'admin') {
                            window.location.href = 'admin.php';
                        } else {
                            window.location.href = 'dashboard.php';
                        }
                    }, 800);
                } else {
                    showToast(res.message || 'Invalid credentials.', 'error');
                    resetLoginBtn();
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Invalid credentials. Please try again.';
                showToast(msg, 'error');
                resetLoginBtn();
            }
        });
    });

    function resetLoginBtn() {
        $('#loginBtn .btn-label').removeClass('d-none');
        $('#loginBtn .btn-spin').addClass('d-none');
        $('#loginBtn').prop('disabled', false);
    }
}

// ─── REGISTER PAGE ─────────────────────────────────────────────
function initRegisterPage() {

    // Password strength feedback
    $('#regPass').on('input', function() {
        const pw = $(this).val();
        const s  = checkStrength(pw);
        $('#strengthBar').css({ width: s.pct + '%', background: s.color });
        $('#strengthLabel').text(s.label).css('color', s.color);
    });

    // Real-time field validation
    $('#regName').on('blur', function() {
        setFieldError('regName', 'nameErr', !$(this).val().trim() ? 'Full name is required.' : '');
    });
    $('#regEmail').on('blur', function() {
        const v = $(this).val().trim();
        setFieldError('regEmail', 'regEmailErr', !v ? 'Email is required.' : !isValidEmail(v) ? 'Enter a valid email.' : '');
    });
    $('#regPass').on('blur', function() {
        const v = $(this).val();
        setFieldError('regPass', 'regPassErr', !v ? 'Password is required.' : v.length < 6 ? 'At least 6 characters required.' : '');
    });
    $('#regConfirm').on('blur', function() {
        const pass    = $('#regPass').val();
        const confirm = $(this).val();
        setFieldError('regConfirm', 'confirmErr', !confirm ? 'Please confirm your password.' : confirm !== pass ? 'Passwords do not match.' : '');
    });

    // Form submit
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();

        const name       = $('#regName').val().trim();
        const email      = $('#regEmail').val().trim();
        const password   = $('#regPass').val();
        const confirm    = $('#regConfirm').val();
        const department = $('#regDept').val().trim();
        const phone      = $('#regPhone').val().trim();

        let ok = true;
        ok = setFieldError('regName',    'nameErr',    !name     ? 'Full name is required.' : '') && ok;
        ok = setFieldError('regEmail',   'regEmailErr', !email   ? 'Email is required.' : !isValidEmail(email) ? 'Enter a valid email.' : '') && ok;
        ok = setFieldError('regPass',    'regPassErr',  !password ? 'Password is required.' : password.length < 6 ? 'Must be 6+ characters.' : '') && ok;
        ok = setFieldError('regConfirm', 'confirmErr',  password !== confirm ? 'Passwords do not match.' : '') && ok;
        if (!ok) return;

        $('#registerBtn .btn-label').addClass('d-none');
        $('#registerBtn .btn-spin').removeClass('d-none');
        $('#registerBtn').prop('disabled', true);

        $.ajax({
            url:  '/SSRCMS/api/register.php',
            type: 'POST',
            data: { name, email, password, department, phone },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    showToast('Account created! Redirecting to login…', 'success');
                    setTimeout(() => { window.location.href = '/SSRCMS/index.php'; }, 1200);
                } else {
                    showToast(res.message || 'Registration failed.', 'error');
                    resetRegBtn();
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Server error. Please try again.';
                showToast(msg, 'error');
                resetRegBtn();
            }
        });
    });

    function resetRegBtn() {
        $('#registerBtn .btn-label').removeClass('d-none');
        $('#registerBtn .btn-spin').addClass('d-none');
        $('#registerBtn').prop('disabled', false);
    }
}
