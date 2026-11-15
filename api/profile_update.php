<?php
/**
 * api/profile_update.php — Save the student's profile details.
 */

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../Student-Portal/User_Profile.php');
}

$fullName     = trim($_POST['userName'] ?? '');
$firstName    = trim($_POST['firstName'] ?? '');
$lastName     = trim($_POST['lastName'] ?? '');
$email        = trim($_POST['email'] ?? '');
$dateOfBirth  = trim($_POST['dateOfBirth'] ?? '');
$mobileNumber = trim($_POST['mobileNumber'] ?? '');

if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash_set('profile_error', 'Name and a valid email are required.');
    redirect('../Student-Portal/User_Profile.php');
}

// Email must stay unique (another account may not use it)
$stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
$stmt->execute([$email, $user['id']]);

if ($stmt->fetch()) {
    flash_set('profile_error', 'This email is already used by another account.');
    redirect('../Student-Portal/User_Profile.php');
}

$stmt = db()->prepare(
    'UPDATE users
     SET full_name = ?, first_name = ?, last_name = ?, email = ?,
         date_of_birth = ?, mobile_number = ?
     WHERE id = ?'
);
$stmt->execute([
    $fullName, $firstName, $lastName, $email,
    $dateOfBirth !== '' ? $dateOfBirth : null,
    $mobileNumber,
    $user['id'],
]);

flash_set('profile_notice', 'Profile updated successfully.');
redirect('../Student-Portal/User_Profile.php');
