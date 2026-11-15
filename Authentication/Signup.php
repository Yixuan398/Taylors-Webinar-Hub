<?php
// Sign-up page — bounce users who are already logged in.
require_once __DIR__ . '/../includes/auth.php';

if (current_user()) {
    redirect('../Event-Discovery/Home.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Taylor's Webinar & Guest Speaker Hub</title>
    <link rel="stylesheet" href="../assets/css/Signup.css">
    <link rel="stylesheet" href="../assets/css/backend.css">
    <script src="../assets/js/Signup.js" defer></script>
</head>
<body>

    <!-- Header -->
    <header class="site-header">
        <div class="brand">
            <img
                src="../assets/images/logo.png"
                alt="Logo"
                class="brand-logo"
            >

            <div class="brand-text">
                <span class="brand-name">Taylor's</span>
                <span class="brand-subtitle">
                    WEBINAR &amp; GUEST SPEAKER HUB
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">

        <!-- Introduction -->
        <section class="intro">
            <p class="eyebrow">GOOD TIMES START HERE</p>

            <h1>Discover. Learn. Connect.</h1>

            <p class="intro-description">
                Join Taylor's Webinar &amp; Guest Speaker Hub
                to discover exciting talks and events.
            </p>
        </section>

        <!-- Sign Up Form -->
        <section class="signup-card">
            <h2>Sign Up</h2>

            <form id="signupForm" method="post" action="../api/signup.php">

                <?php if ($error = flash_get('signup_error')): ?>
                    <p class="form-message error"><?= e($error) ?></p>
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Alex Morgan"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="alex.morgan@sd.taylors.edu.my"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••••••"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-password"
                            data-target="password"
                            aria-label="Show password"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10.58 10.59a2 2 0 0 0 2.83 2.82"/>
                                <path d="M9.88 5.09A10.94 10.94 0 0 1 12 4.9c5 0 9 4.6 10 7.1a13.7 13.7 0 0 1-3.1 4.1"/>
                                <path d="M6.6 6.6C3.8 8.1 2.4 10.6 2 12c1 2.5 5 7.1 10 7.1a10.4 10.4 0 0 0 5.4-1.5"/>
                                <path d="M3 3l18 18"/>
                                </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm-password">
                        Confirm password
                    </label>

                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="confirm-password"
                            name="confirm_password"
                            placeholder="••••••••••••"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-password"
                            data-target="confirm-password"
                            aria-label="Show password"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10.58 10.59a2 2 0 0 0 2.83 2.82"/>
                                <path d="M9.88 5.09A10.94 10.94 0 0 1 12 4.9c5 0 9 4.6 10 7.1a13.7 13.7 0 0 1-3.1 4.1"/>
                                <path d="M6.6 6.6C3.8 8.1 2.4 10.6 2 12c1 2.5 5 7.1 10 7.1a10.4 10.4 0 0 0 5.4-1.5"/>
                                <path d="M3 3l18 18"/>
                                </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="create-button">
                    Create Account
                </button>

            </form>

            <p class="login-link">
                Already have an account?
                <a href="Login.php">Log in</a>
            </p>

        </section>

    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <p>
            &copy; 2026 Taylor's Webinar &amp; Guest Speaker Hub
        </p>

        <div class="footer-links">
            <a href="#">Privacy policy</a>
            <a href="#">Terms of use</a>
        </div>
    </footer>

    <script src="../assets/js/Signup.js"></script>

</body>
</html>
