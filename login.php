<?php
/**
 * NexusBrew - Login Page
 *
 * ┌──────────────────────────────────────────────────────────────┐
 * │ INTENTIONAL VULNERABILITY #1: Hard-coded Credentials        │
 * │ CWE-798 | Detected by: Gitleaks                             │
 * │                                                              │
 * │ The credentials below are intentionally hard-coded in the   │
 * │ PHP source file so that Gitleaks (secret scanner) can       │
 * │ detect them during the CI/CD pipeline scan.                  │
 * │                                                              │
 * │ Email    : admin@nexusbrew.local                            │
 * │ Password : Admin123!                                         │
 * └──────────────────────────────────────────────────────────────┘
 *
 * Additional vulnerabilities on this page:
 * - No CSRF token on login form (CWE-352)
 * - No rate limiting / brute-force protection
 * - No session regeneration after login
 */

session_start();

// Handle logout
if (isset($_GET['logout'])) {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
    header('Location: login.php?msg=logged_out');
    exit;
}

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/config.php';

$error   = '';
$success = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $success = 'You have been successfully logged out.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // VULNERABILITY: No input sanitization before comparison
    $inputEmail    = $_POST['email']    ?? '';
    $inputPassword = $_POST['password'] ?? '';

    // ══════════════════════════════════════════════════════════
    // VULNERABILITY: Hard-coded credential comparison
    // CWE-798: Use of Hard-coded Credentials
    // Tool: Gitleaks
    //
    // Gitleaks pattern that will trigger:
    //   - email:    admin@nexusbrew.local
    //   - password: Admin123!
    //
    // In a secure implementation, this should:
    //   1. Look up user in database
    //   2. Verify password with password_verify()
    //   3. Use prepared statements
    //   4. Implement rate limiting
    // ══════════════════════════════════════════════════════════
    if ($inputEmail === 'admin@nexusbrew.local' && $inputPassword === 'Admin123!') {
        // VULNERABILITY: No session_regenerate_id() called (session fixation risk)
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email']     = $inputEmail;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — NexusBrew Admin</title>

    <!-- VULNERABILITY: Missing Security Headers (configured in PHP, not .htaccess)     -->
    <!-- CWE-693 | Detected by: Nuclei                                                  -->
    <!-- Missing: Content-Security-Policy, X-Frame-Options, X-Content-Type-Options      -->
    <!-- In production these should be set via Apache headers or PHP header() calls     -->

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        .login-page { background: #0A0604; }
    </style>
</head>
<body class="login-page">

    <!-- Background -->
    <div class="login-bg-blur"></div>

    <!-- Centered Card -->
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height:100vh;">
            <div class="col-12" style="max-width:440px;">

                <div class="login-card animate-fadeup">

                    <!-- Logo -->
                    <div class="login-logo">
                        <div class="login-logo-icon">☕</div>
                        <div class="login-title">NexusBrew</div>
                        <div class="login-subtitle">Administrator Portal</div>
                    </div>

                    <!-- DevSecOps Vulnerability Notice -->
                    <!-- Email: admin@nexusbrew.local &nbsp;|&nbsp; Password: Admin123! -->
                    <!-- Success / Error Messages -->
                    <?php if ($success): ?>
                        <div class="alert-success auto-dismiss">
                            <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert-error auto-dismiss">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <!-- VULNERABILITY: No CSRF token on this form -->
                    <form method="POST" action="login.php" autocomplete="off">

                        <div class="nb-form-group">
                            <label class="nb-label" for="email">
                                <i class="bi bi-envelope me-1"></i> Email Address
                            </label>
                            <div class="nb-input-icon">
                                <i class="icon bi bi-envelope"></i>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="nb-input"
                                    placeholder="admin@nexusbrew.local"
                                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="nb-form-group">
                            <label class="nb-label" for="password">
                                <i class="bi bi-lock me-1"></i> Password
                            </label>
                            <div class="nb-input-icon">
                                <i class="icon bi bi-lock"></i>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="nb-input"
                                    placeholder="Enter your password"
                                    required
                                >
                            </div>
                        </div>

                        <button type="submit" id="loginBtn" class="btn-login mt-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>
                            Sign In to Dashboard
                        </button>

                    </form>

                    <!-- Back to store -->
                    <div class="text-center mt-4">
                        <a href="index.php" class="text-decoration-none" style="font-size:0.82rem;color:var(--text-muted);">
                            <i class="bi bi-arrow-left me-1"></i>
                            Back to Storefront
                        </a>
                    </div>

                </div><!-- .login-card -->

                <!-- Footer note -->
                <p class="text-center mt-3" style="font-size:0.72rem;color:var(--text-muted);">
                    NexusBrew v1.0.0 &nbsp;·&nbsp; DevSecOps Demo Application
                </p>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
