<?php
/**
 * api/login.php — Authenticate a user and start the session.
 */

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../Authentication/Login.php');
}
require_valid_csrf();

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

if (!empty($_POST['remember_me'])) {
    setcookie('remember_email', $email, [
        'expires' => time() + 30 * 24 * 60 * 60,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
} else {
    setcookie('remember_email', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

$previousLogin = $_COOKIE['last_login_at'] ?? null;
if (is_string($previousLogin) && str_contains($previousLogin, '|')) {
    [$cookieUserId, $cookieTimestamp] = explode('|', $previousLogin, 2);
    if ($cookieUserId === (string) $user['id']
        && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $cookieTimestamp)) {
        $parsedPreviousLogin = DateTime::createFromFormat('!Y-m-d H:i:s', $cookieTimestamp);
        if ($parsedPreviousLogin && $parsedPreviousLogin->format('Y-m-d H:i:s') === $cookieTimestamp) {
            $_SESSION['previous_login_at'] = $cookieTimestamp;
        }
    }
}
setcookie('last_login_at', (string) $user['id'] . '|' . date('Y-m-d H:i:s'), [
    'expires' => time() + 365 * 24 * 60 * 60,
    'path' => '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);

// Admins land on the admin dashboard, students on the home page
if ($user['role'] === 'admin') {
    redirect('../Admin-Management/Dashboard.php');
}

redirect('../Event-Discovery/Home.php');
