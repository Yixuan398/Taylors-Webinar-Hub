<?php
/**
 * api/login.php — Authenticate a user and start the session.
 */

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../Authentication/Login.php');
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Generic message on purpose — do not reveal which part was wrong
if (!$user || !password_verify($password, $user['password_hash'])) {
    flash_set('login_error', 'Incorrect email or password.');
    redirect('../Authentication/Login.php');
}

// Prevent session fixation, then store the login state
session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['id'];

// ---- Cookies ----
// 1) "Remember me": keep the email in a cookie for 30 days so the login
//    form can prefill it next time. Only the email is stored, never the password.
if (!empty($_POST['remember_me'])) {
    setcookie('remember_email', $email, [
        'expires'  => time() + 30 * 24 * 60 * 60,
        'path'     => '/',
        'httponly' => true,   // not readable from JavaScript (XSS protection)
        'samesite' => 'Lax',
    ]);
} else {
    // Unchecked: remove any previously stored email cookie
    setcookie('remember_email', '', time() - 3600, '/');
}

// 2) Store the login time so the home page can show "last login".
//    (Skip on the very first login — there is no previous visit yet.)
if (!empty($_COOKIE['last_login_at'])) {
    $_SESSION['previous_login_at'] = $_COOKIE['last_login_at'];
}
setcookie('last_login_at', date('Y-m-d H:i:s'), [
    'expires'  => time() + 365 * 24 * 60 * 60,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Admins land on the admin dashboard, students on the home page
if ($user['role'] === 'admin') {
    redirect('../Admin-Management/Dashboard.php');
}

redirect('../Event-Discovery/Home.php');
