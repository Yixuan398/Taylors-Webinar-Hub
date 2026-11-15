<?php
// User Profile — prefilled from the database, saves via api/profile_update.php.
require_once __DIR__ . '/../includes/auth.php';
$user = require_login();

$initials = strtoupper(
    mb_substr($user['first_name'] ?: $user['full_name'], 0, 1)
    . mb_substr($user['last_name'] ?? '', 0, 1)
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | Taylor's Webinar & Guest Speaker Hub</title>
    <link rel="stylesheet" href="../assets/css/User_Profile.css">
    <link rel="stylesheet" href="../assets/css/backend.css">
    <script src="../assets/js/User_Profile.js" defer></script>
</head>
<body>

    <!-- Header -->
    <header class="site-header">
        <div class="header-content">
            <a href="../Event-Discovery/Home.php" class="brand">
                <img src="../assets/images/logo.png"
                     alt="Taylor's University Logo"
                     class="brand-logo">

                <div class="brand-text">
                    <span class="brand-name">Taylor’s</span>
                    <span class="brand-subtitle">WEBINAR &amp; GUEST SPEAKER HUB</span>
                </div>
            </a>

            <nav class="header-nav">
                <a href="My_Events.php" class="my-events-link">My Events</a>

                <a href="User_Profile.php" class="user-profile-link">
                    <span class="header-avatar"><?= htmlspecialchars(strtoupper(implode('', array_map(fn($part) => substr($part, 0, 1), array_slice(preg_split('/\s+/', trim($user['full_name'])), 0, 2))))) ?></span>
                    <span class="user-name"><?= e($user['full_name']) ?></span>

                    <svg class="chevron-icon" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor"
                         stroke-width="1.7" stroke-linecap="round"
                         stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6"/>
                    </svg>
                </a>

                <a href="../api/logout.php" class="logout-link">
                    Log out
                </a>
            </nav>
        </div>
    </header>

    <main class="main-content">

        <!-- Profile Card -->
        <aside class="profile-card">
            <div class="profile-avatar-container">
                <div class="profile-avatar" id="profileAvatar">
                    <span id="profileInitials"><?= e($initials) ?></span>
                    <img id="profileImage" alt="Profile photo" hidden>
                </div>

                <button type="button" class="camera-button"
                        id="changePhotoButton" aria-label="Change profile photo">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 7h4l2-3h4l2 3h4a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                </button>

                <input type="file" id="profilePhotoInput"
                       accept="image/*" hidden>
            </div>

            <h1 id="profileDisplayName"><?= e($user['full_name']) ?></h1>
            <?php if ($notice = flash_get('profile_notice')): ?>
                <p class="form-message success"><?= e($notice) ?></p>
            <?php endif; ?>
            <?php if ($error = flash_get('profile_error')): ?>
                <p class="form-message error"><?= e($error) ?></p>
            <?php endif; ?>
            <?php if ($error = flash_get('password_error')): ?>
                <p class="form-message error"><?= e($error) ?></p>
            <?php endif; ?>

            <p class="profile-email" id="profileDisplayEmail">
                alexlee@student.taylors.edu.my
            </p>
            <p class="joined-date">Joined since Sep 2025</p>

            <button type="button" class="change-password-button"
                    id="changePasswordButton">
                Change Password
            </button>

            <a href="../Authentication/Login.php" class="profile-logout-button">
                Logout
            </a>
        </aside>

        <!-- User Details -->
        <section class="details-card">
            <div class="details-header">
                <div>
                    <h2>User Details</h2>
                    <p>Keep your personal and academic information up to date.</p>
                </div>

                <button type="button" id="editProfileButton" class="edit-button">
                    Edit profile ↗
                </button>
            </div>

            <form id="profileForm" method="post" action="../api/profile_update.php">

                <!-- Personal Information -->
                <section class="information-section">
                    <h3>PERSONAL INFORMATION</h3>

                    <div class="information-grid">
                        <div class="field">
                            <label for="userName">User Name <span>*</span></label>
                            <input type="text" id="userName" name="userName"
                                   value="<?= e($user['full_name']) ?>" required disabled>
                        </div>

                        <div class="field">
                            <label for="dateOfBirth">Date of Birth</label>
                            <input type="date" id="dateOfBirth" name="dateOfBirth"
                                   value="<?= e($user['date_of_birth'] ?? '') ?>" disabled>
                        </div>

                        <div class="field">
                            <label for="firstName">First Name <span>*</span></label>
                            <input type="text" id="firstName" name="firstName"
                                   value="<?= e($user['first_name'] ?? '') ?>" required disabled>
                        </div>

                        <div class="field">
                            <label for="lastName">Last Name <span>*</span></label>
                            <input type="text" id="lastName" name="lastName"
                                   value="<?= e($user['last_name'] ?? '') ?>" required disabled>
                        </div>

                        <div class="field">
                            <label for="email">Email address <span>*</span></label>
                            <input type="email" id="email" name="email"
                                   value="<?= e($user['email']) ?>"
                                   required disabled>
                        </div>

                        <div class="field">
                            <label for="mobileNumber">Mobile number</label>
                            <input type="tel" id="mobileNumber" name="mobileNumber"
                                   value="<?= e($user['mobile_number'] ?? '') ?>" disabled>
                        </div>
                    </div>
                </section>

                <!-- Academic Information -->
                <section class="information-section academic-section">
                    <h3>ACADEMIC INFORMATION</h3>

                    <div class="information-grid">
                        <div class="field">
                            <label for="studentId">Student ID</label>
                            <input type="text" id="studentId"
                                   value="123456" disabled>
                        </div>

                        <div class="field">
                            <label for="faculty">Faculty</label>
                            <input type="text" id="faculty"
                                   value="School of Computing" disabled>
                        </div>

                        <div class="field">
                            <label for="intake">Intake</label>
                            <input type="text" id="intake"
                                   value="September 2025" disabled>
                        </div>

                        <div class="field">
                            <label for="programme">Programme</label>
                            <input type="text" id="programme"
                                   value="Bachelor of Computer Science (Hons)"
                                   disabled>
                        </div>

                        <div class="field">
                            <label for="yearOfStudy">Year of Study</label>
                            <input type="text" id="yearOfStudy"
                                   value="Year 2" disabled>
                        </div>
                    </div>
                </section>

                <div class="form-actions" id="formActions" hidden>
                    <button type="button" id="cancelEditButton"
                            class="cancel-button">
                        Cancel
                    </button>

                    <button type="submit" class="save-button">
                        Save changes
                    </button>
                </div>

            </form>
        </section>

    </main>

    <!-- Change Password Dialog -->
    <dialog id="passwordDialog" class="password-dialog">
        <form id="passwordForm" method="post" action="../api/password_change.php">
            <div class="dialog-header">
                <h2>Change Password</h2>
                <button type="button" id="closePasswordDialog"
                        class="close-dialog" aria-label="Close">
                    &times;
                </button>
            </div>

            <label for="currentPassword">Current Password</label>
            <input type="password" id="currentPassword" name="currentPassword" required>

            <label for="newPassword">New Password</label>
            <input type="password" id="newPassword" name="newPassword" minlength="8" required>

            <label for="confirmPassword">Confirm New Password</label>
            <input type="password" id="confirmPassword" name="confirmPassword" minlength="8" required>

            <p class="password-error" id="passwordError" hidden>
                New passwords do not match.
            </p>

            <div class="dialog-actions">
                <button type="button" id="cancelPasswordButton"
                        class="cancel-button">
                    Cancel
                </button>
                <button type="submit" class="save-button">
                    Change Password
                </button>
            </div>
        </form>
    </dialog>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-content">
            <span>© 2026 Taylor’s Webinar &amp; Guest Speaker Hub</span>

            <div class="footer-links">
                <a href="#">Privacy policy</a>
                <a href="#">Terms of use</a>
            </div>
        </div>
    </footer>

</body>
</html>
