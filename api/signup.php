<?php
/**
 * api/signup.php — Handle the registration form.
 * Validates input, hashes the password and creates a student account.
 */

require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../Authentication/Signup.php');
}

$name            = trim($_POST['name'] ?? '');
$email           = trim($_POST['email'] ?? '');
$password        = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

// ---- Server-side validation ----
if ($name === '' || $email === '' || $password === '') {
    flash_set('signup_error', 'All fields are required.');
    redirect('../Authentication/Signup.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('signup_error', 'Please enter a valid email address.');
    redirect('../Authentication/Signup.php');
}

if (strlen($password) < 8) {
    flash_set('signup_error', 'Password must be at least 8 characters long.');
    redirect('../Authentication/Signup.php');
}

if ($password !== $confirmPassword) {
    flash_set('signup_error', 'Passwords do not match.');
    redirect('../Authentication/Signup.php');
}

// ---- Check for duplicate email ----
$stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);

if ($stmt->fetch()) {
    flash_set('signup_error', 'An account with this email already exists.');
    redirect('../Authentication/Signup.php');
}

// ---- Create the account (passwords are never stored in plain text) ----
$hash = password_hash($password, PASSWORD_DEFAULT);

// Split the display name into first/last for the profile page
$parts     = preg_split('/\s+/', $name, 2);
$firstName = $parts[0];
$lastName  = $parts[1] ?? '';

$stmt = db()->prepare(
    'INSERT INTO users (full_name, email, password_hash, role, first_name, last_name)
     VALUES (?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$name, $email, $hash, 'student', $firstName, $lastName]);

// Log the new user in straight away
$_SESSION['user_id'] = (int) db()->lastInsertId();

flash_set('home_notice', 'Welcome, ' . $name . '! Your account is ready.');
redirect('../Event-Discovery/Home.php');
