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

// Admins land on the admin dashboard, students on the home page
if ($user['role'] === 'admin') {
    redirect('../Admin-Management/Dashboard.php');
}

redirect('../Event-Discovery/Home.php');
