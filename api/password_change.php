<?php
/**
 * api/password_change.php — Change the logged-in user's password.
 * Verifies the current password before applying the new one.
 */

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../Student-Portal/User_Profile.php');
}

$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword     = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

if (!password_verify($currentPassword, $user['password_hash'])) {
    flash_set('password_error', 'Your current password is incorrect.');
    redirect('../Student-Portal/User_Profile.php');
}

if (strlen($newPassword) < 8) {
    flash_set('password_error', 'New password must be at least 8 characters long.');
    redirect('../Student-Portal/User_Profile.php');
}

if ($newPassword !== $confirmPassword) {
    flash_set('password_error', 'New passwords do not match.');
    redirect('../Student-Portal/User_Profile.php');
}

$stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
$stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);

flash_set('profile_notice', 'Password changed successfully.');
redirect('../Student-Portal/User_Profile.php');
